<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
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

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class,'payment_method_id');
    }

}
