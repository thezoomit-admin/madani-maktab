<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductRequest;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index()
    {
        $categories = $this->productService->index();
        return api_response($categories, 'Product fetched successfully.');
    }

    public function show($id)
    {
        try {
            $category = $this->productService->show($id);
            return api_response($category, 'Product fetched successfully.');
        } catch (\Exception $e) {
            return api_response(null, $e->getMessage(), false, $e->getCode());
        }
    }

    public function store(ProductRequest $request)
    {
        try {
            $data = $request->validated();
            $category = $this->productService->store($data);
            return api_response($category, 'Product created successfully.');
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
