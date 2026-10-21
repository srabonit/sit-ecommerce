<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductGallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Universal Upload Helper for Products
     */
    protected function uploadProductFile($file, string $subFolder): string
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $relativeDir = trim($subFolder, '/');

        if (is_dir(base_path('../public_html'))) {
            $baseDir = base_path('../public_html/uploads/' . $relativeDir);
        } else {
            $baseDir = public_path('uploads/' . $relativeDir);
        }

        if (!file_exists($baseDir)) {
            @mkdir($baseDir, 0777, true);
        }

        $file->move($baseDir, $filename);

        return 'uploads/' . $relativeDir . '/' . $filename;
    }

    /**
     * Universal Delete Helper for Products
     */
    protected function deleteProductFile(?string $relativePath): void
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

    /**
     * Display a listing of products with relationships.
     */
    public function index(): JsonResponse
    {
        $products = Product::with(['galleries', 'variants'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'Products fetched successfully.',
            'data' => $products
        ], 200);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Auto-generate unique slug if not explicitly passed
            $data['slug'] = !empty($data['slug']) 
                ? Str::slug($data['slug']) 
                : Str::slug($data['title']) . '-' . time();

            // Handle main thumbnail upload
            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = $this->uploadProductFile($request->file('thumbnail'), 'products/thumbnails');
            }

            // Create base product record
            $product = Product::create($data);

            // Handle multiple gallery uploads up to 10 files
            if ($request->has('gallery_images')) {
                foreach ($request->gallery_images as $galleryItem) {
                    if (isset($galleryItem['file'])) {
                        $path = $this->uploadProductFile($galleryItem['file'], 'products/gallery');
                        ProductGallery::create([
                            'product_id'  => $product->id,
                            'image_path'  => $path,
                            'image_title' => $galleryItem['title'] ?? null,
                            'image_alt'   => $galleryItem['alt'] ?? null,
                        ]);
                    }
                }
            }

            // Store product variants if variable structure
            if ($request->product_struct === 'variable' && $request->has('variants')) {
                foreach ($request->variants as $variant) {
                    $product->variants()->create($variant);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Product created successfully.',
                'data' => $product->load(['galleries', 'variants'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Product creation failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with(['galleries', 'variants'])->find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $product
        ], 200);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Replace old thumbnail if new file is uploaded
            if ($request->hasFile('thumbnail')) {
                if ($product->thumbnail) {
                    $this->deleteProductFile($product->thumbnail);
                }
                $data['thumbnail'] = $this->uploadProductFile($request->file('thumbnail'), 'products/thumbnails');
            }

            $product->update($data);

            // Append new gallery images
            if ($request->has('gallery_images')) {
                foreach ($request->gallery_images as $galleryItem) {
                    if (isset($galleryItem['file'])) {
                        $path = $this->uploadProductFile($galleryItem['file'], 'products/gallery');
                        ProductGallery::create([
                            'product_id'  => $product->id,
                            'image_path'  => $path,
                            'image_title' => $galleryItem['title'] ?? null,
                            'image_alt'   => $galleryItem['alt'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully.',
                'data' => $product->load(['galleries', 'variants'])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Product update failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product and its media from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::with('galleries')->find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found.'
            ], 404);
        }

        // Clean up thumbnail from disk
        if ($product->thumbnail) {
            $this->deleteProductFile($product->thumbnail);
        }

        // Clean up gallery images from disk
        foreach ($product->galleries as $gallery) {
            $this->deleteProductFile($gallery->image_path);
        }

        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product and associated assets deleted successfully.'
        ], 200);
    }
}