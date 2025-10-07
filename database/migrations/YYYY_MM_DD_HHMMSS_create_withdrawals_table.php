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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 10, 4);
            $table->string('method'); // e.g., 'PayPal', 'Coinbase', 'Amazon Gift Card', 'Payeer'
            $table->string('account_details'); // e.g., PayPal email, crypto wallet address, Amazon email
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // Admin-managed status
            $table->text('admin_notes')->nullable(); // Notes from admin about the withdrawal
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
