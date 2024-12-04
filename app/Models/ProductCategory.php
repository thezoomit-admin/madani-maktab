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



    protected static function booted()
    {
        static::creating(function ($category) {
            $category->slug = $category->generateUniqueSlug($category->name, $category->company_id);
        });

        static::updating(function ($category) { 
            if ($category->isDirty('name')) {
                $category->slug = $category->generateUniqueSlug($category->name, $category->company_id);
            }
        });
    }
 
    private function generateUniqueSlug($name, $companyId)
    {
        $slug = Str::slug($name); 
        $existingSlugCount = self::where('company_id', $companyId)
            ->where('slug', $slug)
            ->count();

        if ($existingSlugCount > 0) {
            $slug = $slug . '-' . ($existingSlugCount + 1);
        }

        return $slug;
    }
}
