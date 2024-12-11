<?php 
namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductCategoryRequest;
use App\Services\ProductCategoryService; 

class ProductCategoryController extends Controller
{
    protected $productCategoryService;

    public function __construct(ProductCategoryService $productCategoryService)
    {
        $this->productCategoryService = $productCategoryService;
    }

    public function index()
    {
        $categories = $this->productCategoryService->index();
        return api_response($categories, 'Product categories fetched successfully.');
    }

    public function show($id)
    {
        try {
            $category = $this->productCategoryService->show($id);
            return api_response($category, 'Product category fetched successfully.');
        } catch (\Exception $e) {
            return api_response(null, $e->getMessage(), false, $e->getCode());
        }
    }

    public function store(ProductCategoryRequest $request)
    {
        try {
            $data = $request->validated();
            $category = $this->productCategoryService->store($data);
            return api_response($category, 'Product category created successfully.');
        } catch (\Exception $e) {
            return api_response(null, $e->getMessage(), false, 500);
        }
    }

    public function update(ProductCategoryRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $category = $this->productCategoryService->update($id, $data);
            return api_response($category, 'Product category updated successfully.');
        } catch (\Exception $e) {
            return api_response(null, $e->getMessage(), false, $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {
            $this->productCategoryService->destroy($id);
            return api_response(null, 'Product category deleted successfully.');
        } catch (\Exception $e) {
            return api_response(null, $e->getMessage(), false, $e->getCode());
        }
    }
}
