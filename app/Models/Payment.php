<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory; 

    protected $fillable = [
        'user_id',
        'student_id',
        'hijri_month_id',
        'reason',
        'amount',
        'paid',
        'due',
        'fee_type',
        'created_by',
        'updated_by',
    ]; 

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
 
    public function user()
    {
        return $this->belongsTo(User::class);
    }
 
    public function hijriMonth()
    {
        return $this->belongsTo(HijriMonth::class);
    }
 
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }  
    
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
}
