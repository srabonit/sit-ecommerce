<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubCategoryRequest;
use App\Http\Requests\UpdateSubCategoryRequest;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SubCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $subCategories = SubCategory::with('category')->latest()->get();

        return response()->json([
            'status' => true,
            'data' => $subCategories
        ], 200);
    }

    public function store(StoreSubCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);

        $subCategory = SubCategory::create($data);

        return response()->json([
            'status' => true,
            'message' => 'SubCategory created successfully.',
            'data' => $subCategory
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $subCategory = SubCategory::with('category')->find($id);

        if (!$subCategory) {
            return response()->json(['status' => false, 'message' => 'SubCategory not found.'], 404);
        }

        return response()->json(['status' => true, 'data' => $subCategory], 200);
    }

    public function update(UpdateSubCategoryRequest $request, int $id): JsonResponse
    {
        $subCategory = SubCategory::find($id);

        if (!$subCategory) {
            return response()->json(['status' => false, 'message' => 'SubCategory not found.'], 404);
        }

        $data = $request->validated();

        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $subCategory->update($data);

        return response()->json([
            'status' => true,
            'message' => 'SubCategory updated successfully.',
            'data' => $subCategory
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $subCategory = SubCategory::find($id);

        if (!$subCategory) {
            return response()->json(['status' => false, 'message' => 'SubCategory not found.'], 404);
        }

        $subCategory->delete();

        return response()->json(['status' => true, 'message' => 'SubCategory deleted successfully.'], 200);
    }
}