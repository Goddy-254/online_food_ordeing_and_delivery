<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/** Server-side client for the Daraja OAuth and STK Push endpoints. */
class MpesaService
{
    private function baseUrl(): string
    {
        $environment = config('mpesa.env', 'sandbox');
        $url = config("mpesa.base_url.{$environment}");

        if (!$url) {
            throw new RuntimeException('M-Pesa environment must be sandbox or production.');
        }

        return rtrim($url, '/');
    }

    /** Fetch and briefly cache the OAuth token to avoid requesting one per call. */
    public function accessToken(): string
    {
        $cacheKey = 'mpesa.access_token.'.config('mpesa.env', 'sandbox');

        if ($token = Cache::get($cacheKey)) {
            return $token;
        }

        $key = config('mpesa.consumer_key');
        $secret = config('mpesa.consumer_secret');

        if (!$key || !$secret) {
            throw new RuntimeException('M-Pesa API credentials are not configured.');
        }

        $response = Http::withBasicAuth($key, $secret)
            ->acceptJson()
            ->timeout(15)
            ->get($this->baseUrl().config('mpesa.endpoints.oauth'));

        $token = $response->json('access_token');

        if (!$response->successful() || !$token) {
            throw new RuntimeException('Safaricom did not return an access token.');
        }

        $expiresIn = max(60, ((int) $response->json('expires_in', 3600)) - 60);
        Cache::put($cacheKey, $token, now()->addSeconds($expiresIn));

        return $token;
    }

    /** Ask Safaricom to send a payment prompt to the customer. */
    public function sendStkPush(string $phone, int $amount, string $reference): array
    {
        $shortcode = (string) config('mpesa.shortcode');
        $passkey = config('mpesa.passkey');
        $callbackUrl = config('mpesa.callback_url');

        if (!$shortcode || !$passkey || !$callbackUrl) {
            throw new RuntimeException('M-Pesa shortcode, passkey, or callback URL is missing.');
        }

        // Safaricom's STK timestamp is in East Africa local time.
        $timestamp = now('Africa/Nairobi')->format('YmdHis');
        $password = base64_encode($shortcode.$passkey.$timestamp);

        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->timeout(20)
            ->post($this->baseUrl().config('mpesa.endpoints.stk_push'), [
                'BusinessShortCode' => $shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => config('mpesa.transaction_type', 'CustomerPayBillOnline'),
                'Amount' => $amount,
                'PartyA' => $phone,
                'PartyB' => $shortcode,
                'PhoneNumber' => $phone,
                'CallBackURL' => $callbackUrl,
                'AccountReference' => $reference,
                'TransactionDesc' => 'Food order',
            ]);

        $data = $response->json() ?? [];

        if ($response->clientError()) {
            // A 4xx response is an explicit rejection, so the customer can safely retry.
            throw new MpesaRequestRejected(
                $data['errorMessage'] ?? 'Safaricom rejected the STK request. Check the M-Pesa settings.'
            );
        }

        if (!$response->successful() || empty($data['CheckoutRequestID'])) {
            // A server or malformed response can be ambiguous: retain it for reconciliation.
            throw new RuntimeException('Safaricom did not confirm the STK request.');
        }

        return [
            'merchant_request_id' => $data['MerchantRequestID'] ?? null,
            'checkout_request_id' => $data['CheckoutRequestID'],
            'customer_message' => $data['CustomerMessage'] ?? 'Check your phone to complete payment.',
        ];
    }
}
