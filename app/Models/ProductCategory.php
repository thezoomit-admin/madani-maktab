<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'company_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ]; 
 
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }


  
}
