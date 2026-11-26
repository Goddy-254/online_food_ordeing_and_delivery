<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Daraja returns these IDs before the customer approves the prompt.
            $table->string('merchant_request_id')->nullable()->index();
            $table->string('checkout_request_id')->nullable()->unique();
            $table->string('phone_number', 20)->nullable();
            $table->unsignedInteger('result_code')->nullable();
            $table->text('result_description')->nullable();
            $table->timestamp('paid_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'merchant_request_id',
                'checkout_request_id',
                'phone_number',
                'result_code',
                'result_description',
                'paid_at',
            ]);
        });
    }
};
