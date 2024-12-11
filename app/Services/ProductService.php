<?php 
namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductCategoryRepository;
use App\Models\ProductCategory;
use App\Models\User;
use App\Repositories\ProductRepository;
use Illuminate\Support\Str;

class ProductService
{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index()
    {
        return $this->productRepository->all();
    }

    public function show($id)
    {
        $category = $this->productRepository->find($id); 
        if (!$category) {
            throw new \Exception("Product category not found", 404);
        } 
        return $category;
    }

    public function store($data)
    {
        $user = User::find(1);
        $model = new Product();
        $data['company_id'] = $user->company_id;
        $data['created_by'] = $user->id;
        $data['updated_by'] = null;
        $data['deleted_by'] = null;
        $data['slug'] = getSlug($model,$data['name']);

        return $this->productRepository->create($data);
    }

    public function update($id, $data)
    {
        $category = $this->productCategoryRepository->find($id); 
        $model = new ProductCategory();
        if (!$category) {
            throw new \Exception("Product category not found", 404);
        } 
        $data['updated_by'] = 1; 
        $data['slug'] = getSlug($model,$data['name']);

        return $this->productCategoryRepository->update($category, $data);
    }

    public function destroy($id)
    {
        $category = $this->productCategoryRepository->find($id);

        if (!$category) {
            throw new \Exception("Product category not found", 404);
        }

        return $this->productCategoryRepository->delete($category);
    } 
}
