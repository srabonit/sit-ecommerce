<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * Universal Upload Helper for Brand Logo
     */
    protected function uploadBrandLogo($file): string
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        if (is_dir(base_path('../public_html'))) {
            $baseDir = base_path('../public_html/uploads/brands');
        } else {
            $baseDir = public_path('uploads/brands');
        }

        if (!file_exists($baseDir)) {
            @mkdir($baseDir, 0777, true);
        }

        $file->move($baseDir, $filename);

        return 'uploads/brands/' . $filename;
    }

    /**
     * Universal Delete Helper for Brand Logo
     */
    protected function deleteBrandLogo(?string $relativePath): void
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
        $brands = Brand::latest()->get();

        return response()->json([
            'status' => true,
            'data' => $brands
        ], 200);
    }

    public function store(StoreBrandRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->uploadBrandLogo($request->file('logo'));
        }

        $brand = Brand::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Brand created successfully.',
            'data' => $brand
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['status' => false, 'message' => 'Brand not found.'], 404);
        }

        return response()->json(['status' => true, 'data' => $brand], 200);
    }

    public function update(UpdateBrandRequest $request, int $id): JsonResponse
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['status' => false, 'message' => 'Brand not found.'], 404);
        }

        $data = $request->validated();

        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('logo')) {
            if ($brand->logo) {
                $this->deleteBrandLogo($brand->logo);
            }
            $data['logo'] = $this->uploadBrandLogo($request->file('logo'));
        }

        $brand->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Brand updated successfully.',
            'data' => $brand
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['status' => false, 'message' => 'Brand not found.'], 404);
        }

        if ($brand->logo) {
            $this->deleteBrandLogo($brand->logo);
        }

        $brand->delete();

        return response()->json(['status' => true, 'message' => 'Brand deleted successfully.'], 200);
    }
}