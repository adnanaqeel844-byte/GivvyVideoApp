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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->decimal('current_balance', 10, 4)->default(0.0000); // User's money balance, up to 4 decimal places
            $table->string('referral_code')->unique(); // Unique code for this user to refer others
            $table->unsignedBigInteger('referred_by')->nullable(); // ID of the user who referred this user
            $table->rememberToken();
            $table->timestamps();

            // Foreign key for referral system
            $table->foreign('referred_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
