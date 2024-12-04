<?php 
namespace App\Repositories;

use App\Models\ProductCategory;

class ProductCategoryRepository
{
    public function all()
    {
        return ProductCategory::select('id', 'name', 'slug', 'status')
            ->with('company:id,name')
            ->paginate(15);
    }

    public function find($id)
    {
        return ProductCategory::with('company:id,name')->find($id);
    }

    public function create(array $data)
    {
        return ProductCategory::create($data);
    }

    public function update(ProductCategory $category, array $data)
    {
        return $category->update($data);
    }

    public function delete(ProductCategory $category)
    {
        return $category->delete();
    }
}
