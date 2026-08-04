<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $fillable = [
        'campaign_id',
        'user_id',
        'amount',
        'status',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'payment_type',
        'payment_url',
        'donor_name',
        'is_anonymous',
        'message',
        'paid_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'paid_at'      => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}