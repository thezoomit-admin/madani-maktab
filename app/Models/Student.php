<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory; 

    protected $fillable = [
        'user_id', 'reg_id', 'jamaat', 'average_marks', 'status'
    ];


   public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enroles()
    {
        return $this->hasMany(Enrole::class);
    }
 
    public function enroleByYear($year)
    {
        return $this->hasOne(Enrole::class)->where('year', $year);
    }

}
