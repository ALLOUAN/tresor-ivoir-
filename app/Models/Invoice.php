<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'number', 'payment_id', 'provider_id',
        'amount_ht', 'tax_rate', 'tax_amount', 'amount_ttc',
        'pdf_url', 'issued_at', 'due_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_ht' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'amount_ttc' => 'decimal:2',
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
        ];
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}
