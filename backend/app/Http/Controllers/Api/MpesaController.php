<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class MpesaController extends Controller
{
    /** Start an STK Push for an order owned by the signed-in customer. */
    public function initiate(Request $request, string $orderId, MpesaService $mpesa)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $phone = preg_replace('/\D/', '', $validated['phone']);

        if (str_starts_with($phone, '0')) {
            $phone = '254'.substr($phone, 1);
        }

        if (!preg_match('/^254[17]\d{8}$/', $phone)) {
            return response()->json([
                'message' => 'Enter a valid Kenyan mobile number, such as 0712345678 or 254712345678.',
            ], 422);
        }

        // Lock the order while claiming its next payment attempt to prevent duplicate prompts.
        $attempt = DB::transaction(function () use ($request, $orderId, $phone) {
            $order = Order::where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->findOrFail($orderId);

            if ($order->status === 'cancelled') {
                return ['error' => 'This order has been cancelled.', 'status' => 409];
            }

            $latestPayment = $order->payments()->latest('id')->lockForUpdate()->first();

            if ($latestPayment?->status === 'paid') {
                return ['error' => 'This order has already been paid.', 'status' => 409];
            }

            // Do not create another prompt while the current attempt is unresolved.
            if (in_array($latestPayment?->status, ['starting', 'pending', 'review'], true)) {
                return [
                    'error' => 'A payment attempt is still being checked. Please wait before trying again.',
                    'status' => 409,
                    'payment' => $latestPayment->only(['id', 'status']),
                ];
            }

            $amount = (float) $order->total_amount;

            // Daraja STK Push accepts whole shillings. Never silently charge a rounded amount.
            if ($amount < 1 || abs($amount - round($amount)) > 0.001) {
                return [
                    'error' => 'M-Pesa requires a whole-shilling order total. Adjust the menu prices or delivery fee.',
                    'status' => 422,
                ];
            }

            $payment = $latestPayment?->status === 'uninitiated'
                ? $latestPayment
                : $order->payments()->create([
                    'amount' => (int) round($amount),
                    'payment_method' => 'mpesa',
                    'status' => 'starting',
                ]);

            // Mark an existing pre-created payment as claimed before calling Safaricom.
            $payment->update([
                'phone_number' => $phone,
                'status' => 'starting',
            ]);

            return [
                'order' => $order,
                'payment' => $payment,
                'amount' => (int) round($amount),
            ];
        });

        if (isset($attempt['error'])) {
            return response()->json([
                'message' => $attempt['error'],
                'payment' => $attempt['payment'] ?? null,
            ], $attempt['status']);
        }

        $order = $attempt['order'];
        $payment = $attempt['payment'];

        try {
            $push = $mpesa->sendStkPush(
                $phone,
                $attempt['amount'],
                'ORD'.$order->id,
            );

            $payment->update([
                'merchant_request_id' => $push['merchant_request_id'],
                'checkout_request_id' => $push['checkout_request_id'],
                'status' => 'pending',
            ]);
        } catch (MpesaRequestRejected $exception) {
            // Daraja's client-error response confirms it did not accept this prompt.
            Log::warning('M-Pesa rejected the STK request.', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'reason' => $exception->getMessage(),
            ]);

            $payment->update([
                'status' => 'failed',
                'result_description' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'M-Pesa rejected the payment request. Check the payment settings or phone number, then try again.',
                'payment' => $payment->fresh()->only(['id', 'status']),
            ], 422);
        } catch (Throwable $exception) {
            // A timeout can be ambiguous; keep this attempt visible for reconciliation.
            Log::error('M-Pesa STK request failed.', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'error' => $exception->getMessage(),
            ]);

            $payment->update([
                'status' => 'review',
                'result_description' => 'The STK request could not be confirmed. Reconcile before retrying.',
            ]);

            return response()->json([
                'message' => 'We could not confirm the M-Pesa prompt. Please wait or contact the store before retrying.',
                'payment' => $payment->only(['id', 'status']),
            ], 502);
        }

        return response()->json([
            'message' => $push['customer_message'],
            'payment' => $payment->fresh()->only(['id', 'status', 'amount']),
        ], 202);
    }

    /** Return only the latest payment status for the signed-in order owner. */
    public function status(Request $request, string $orderId): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($orderId);
        $payment = $order->payments()->latest('id')->first();

        return response()->json([
            'payment' => $payment?->only([
                'id',
                'status',
                'amount',
                'mpesa_receipt',
                'result_description',
                'paid_at',
            ]),
        ]);
    }

    /** Process Daraja's server-to-server result and make callback updates idempotent. */
    public function callback(Request $request): JsonResponse
    {
        $callback = data_get($request->all(), 'Body.stkCallback');
        $checkoutId = data_get($callback, 'CheckoutRequestID');

        if (!$callback || !$checkoutId) {
            Log::warning('Received an invalid M-Pesa callback payload.');

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        try {
            DB::transaction(function () use ($callback, $checkoutId) {
                $payment = Payment::where('checkout_request_id', $checkoutId)
                    ->lockForUpdate()
                    ->first();

                if (!$payment || $payment->status === 'paid') {
                    return;
                }

                $resultCode = (int) data_get($callback, 'ResultCode', -1);
                $resultDescription = (string) data_get($callback, 'ResultDesc', '');

                if ($resultCode !== 0) {
                    $payment->update([
                        'status' => 'failed',
                        'result_code' => max(0, $resultCode),
                        'result_description' => $resultDescription,
                    ]);

                    return;
                }

                $metadata = collect(data_get($callback, 'CallbackMetadata.Item', []))
                    ->keyBy('Name')
                    ->map(fn ($item) => $item['Value'] ?? null);

                $paidAmount = $metadata->get('Amount');
                $receipt = $metadata->get('MpesaReceiptNumber');
                $paidPhone = $metadata->get('PhoneNumber');

                $detailsMatch = $paidAmount !== null
                    && (float) $paidAmount === (float) $payment->amount
                    && filled($receipt)
                    && (!$paidPhone || (string) $paidPhone === $payment->phone_number);

                $receiptAlreadyUsed = filled($receipt)
                    && Payment::where('mpesa_receipt', $receipt)
                        ->where('id', '!=', $payment->id)
                        ->exists();

                if (!$detailsMatch || $receiptAlreadyUsed) {
                    Log::warning('M-Pesa callback needs payment review.', [
                        'payment_id' => $payment->id,
                        'checkout_request_id' => $checkoutId,
                    ]);

                    $payment->update([
                        'status' => 'review',
                        'result_code' => 0,
                        'result_description' => 'Callback details did not match the expected payment.',
                    ]);

                    return;
                }

                $payment->update([
                    'status' => 'paid',
                    'result_code' => 0,
                    'result_description' => $resultDescription,
                    'mpesa_receipt' => $receipt,
                    'paid_at' => now(),
                ]);
            });
        } catch (Throwable $exception) {
            // A non-success response lets the sender/reconciliation process retry safely.
            Log::error('Could not persist M-Pesa callback.', [
                'checkout_request_id' => $checkoutId,
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Retry processing callback.'], 500);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
