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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('p_qut');
            $table->string('p_price');
            $table->string('imageurl');
            $table->string('p_total');
            $table->string('payment_mode')->default("COD");
            $table->string('p_id');
            $table->string('order_id')->unique();
            $table->string('status')->default(1);
            $table->string('customer_name');
            $table->string('userid')->nullable();

            $table->string('email')->nullable();
            $table->string('mobile');
            $table->string('address');
            $table->string('pincode');
            $table->string('state');
            $table->string('city');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
