<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stripepayments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->string("email");
            $table->string("payment_type");
            $table->string("payment_method");
            $table->string("payment_id");
            $table->string("subscriptionplan");
            $table->string("currency");
            $table->longText("description");
            $table->string("dateofpayment");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripepayments');
    }
};
