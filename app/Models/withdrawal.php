<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'amount',
        'method',            // e.g., 'PayPal', 'Coinbase'
        'account_details',   // e.g., PayPal email, crypto wallet address
        'status',            // e.g., 'pending', 'approved', 'rejected'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'float', // Cast to float
    ];

    /**
     * Get the user that requested the withdrawal.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
