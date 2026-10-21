<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Universal Upload Helper for Category Image
     */
    protected function uploadCategoryImage($file): string
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        if (is_dir(base_path('../public_html'))) {
            $baseDir = base_path('../public_html/uploads/categories');
        } else {
            $baseDir = public_path('uploads/categories');
        }

        if (!file_exists($baseDir)) {
            @mkdir($baseDir, 0777, true);
        }

        $file->move($baseDir, $filename);

        return 'uploads/categories/' . $filename;
    }

    /**
     * Universal Delete Helper for Category Image
     */
    protected function deleteCategoryImage(?string $relativePath): void
    {
        if (empty($relativePath)) return;

        $clean = ltrim($relativePath, '/');
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, 8);
        }

        if (is_dir(base_path('../public_html'))) {
            $file1 = base_path('../public_html/' . $clean);
            $file2 = base_path('../public_html/storage/' . $clean);
            if (file_exists($file1)) @unlink($file1);
            if (file_exists($file2)) @unlink($file2);
        } else {
            $file1 = public_path($clean);
            $file2 = public_path('storage/' . $clean);
            if (file_exists($file1)) @unlink($file1);
            if (file_exists($file2)) @unlink($file2);
        }
    }

    public function index(): JsonResponse
    {
        $categories = Category::with('subCategories')->latest()->get();

        return response()->json([
            'status' => true,
            'data' => $categories
        ], 200);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadCategoryImage($request->file('image'));
        }

        $category = Category::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Category created successfully.',
            'data' => $category
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $category = Category::with('subCategories')->find($id);

        if (!$category) {
            return response()->json(['status' => false, 'message' => 'Category not found.'], 404);
        }

        return response()->json(['status' => true, 'data' => $category], 200);
    }

    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['status' => false, 'message' => 'Category not found.'], 404);
        }

        $data = $request->validated();

        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('image')) {
            if ($category->image) {
                $this->deleteCategoryImage($category->image);
            }
            $data['image'] = $this->uploadCategoryImage($request->file('image'));
        }

        $category->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Category updated successfully.',
            'data' => $category
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['status' => false, 'message' => 'Category not found.'], 404);
        }

        if ($category->image) {
            $this->deleteCategoryImage($category->image);
        }

        $category->delete();

        return response()->json(['status' => true, 'message' => 'Category deleted successfully.'], 200);
    }
}