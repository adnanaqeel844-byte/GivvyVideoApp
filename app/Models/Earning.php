<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',        // e.g., 'video_watch', 'ad_watch', 'referral_bonus'
        'amount',
        'description',
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
     * Get the user that owns the earning.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
