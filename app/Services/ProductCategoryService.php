<?php 
namespace App\Services;

use App\Repositories\ProductCategoryRepository;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Support\Str;

class ProductCategoryService
{
    protected $productCategoryRepository;

    public function __construct(ProductCategoryRepository $productCategoryRepository)
    {
        $this->productCategoryRepository = $productCategoryRepository;
    }

    public function index()
    {
        return $this->productCategoryRepository->all();
    }

    public function show($id)
    {
        $category = $this->productCategoryRepository->find($id);

        if (!$category) {
            throw new \Exception("Product category not found", 404);
        }

        return $category;
    }

    public function store($data)
    {
        $user = User::find(1); 
        $data['company_id'] = $user->company_id;
        $data['created_by'] = 1;
        $data['updated_by'] = null;
        $data['deleted_by'] = null;
        $data['slug'] = $this->generateSlug($data['name']);

        return $this->productCategoryRepository->create($data);
    }

    public function update($id, $data)
    {
        $category = $this->productCategoryRepository->find($id);

        if (!$category) {
            throw new \Exception("Product category not found", 404);
        }

        $data['updated_by'] = 1; // Example for user
        $data['slug'] = $this->generateSlug($data['name'], $category);

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

    protected function generateSlug($name, $category = null)
    {
        $slug = Str::slug($name);
        $existingSlugCount = ProductCategory::where('company_id', $category ? $category->company_id : 1)
                                           ->where('slug', $slug)
                                           ->count();

        if ($existingSlugCount > 0) {
            $slug = $slug . '-' . ($existingSlugCount + 1);
        }

        return $slug;
    }
}
