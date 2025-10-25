<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;  
     protected $fillable = [
        'user_id',
        'expense_category_id',
        'expense_sub_category_id',
        'vendor_id',
        'payment_method_id', 
        'approved_by',
        'amount',
        'total_amount',
        'description',
        'measurement',
        'measurment_unit_id',
        'image',
        'voucher_no',
        'is_approved',
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(ExpenseSubCategory::class, 'expense_sub_category_id');
    }

     public function measurmentUnit()
    {
        return $this->belongsTo(MeasurmentUnit::class, 'measurment_unit_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
