<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id',
        'payment_id',
        'payment_method_id',
        'payer_account',
        'amount',
        'image',
        'is_approved',
        'approved_by',
        'transaction_id',
        'val_id',
        'bank_tran_id',
        'status',
        'card_type',
        'card_no',
        'currency'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
