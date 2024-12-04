<?php

namespace App\Observers;

use App\Models\ProductCategory;

class ProductCategoryObserver
{
     
    public function created(ProductCategory $category): void
    {
        $category->slug = $category->generateUniqueSlug($category->name, $category->company_id);
    }
 
    public function updated(ProductCategory $category): void
    {
        if ($category->isDirty('name')) {
            $category->slug = $category->generateUniqueSlug($category->name, $category->company_id);
        }
    }
 
    public function deleted(ProductCategory $productCategory): void
    {
         
    }
 
    public function restored(ProductCategory $productCategory): void
    {
        //
    }
 
    public function forceDeleted(ProductCategory $productCategory): void
    {
        //
    }
}
