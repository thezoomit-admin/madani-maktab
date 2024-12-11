<?php 
namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public function all()
    {
        return Product::select('id', 'name', 'slug','regular_price','sell_price', 'status')
            ->with('company:id,name')
            ->with('category:id,name')
            ->paginate(15);
    }

    public function find($id)
    {
        return Product::with('company:id,name')->find($id);
    }

    public function create(array $data)
    {
        return Product::create($data);
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
