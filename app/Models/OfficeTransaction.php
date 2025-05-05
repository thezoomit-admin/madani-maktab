<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeTransaction extends Model
{
    use HasFactory;   

    protected $fillable = [
        'type',
        'hijri_month_id',
        'payment_method_id',
        'description',
        'amount',
        'image',
        'is_approved',
        'approved_by',
        'created_by',
    ];

    public function hijriMonth()
    {
        return $this->belongsTo(HijriMonth::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
