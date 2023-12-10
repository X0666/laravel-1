<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('id');
        $desription = $request->input('description');
        $tags = $request->input('tags');
        $categories = $request->input('categories');

        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        if ($id) {
            $product = Product::with(['category', 'galleries'])->find($id);

            if ($product) {
                return ResponseFormatter::success(
                    $product,
                    'Data produk berhasil di ambil'
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Data produk kosong',
                    404
                );
            }
        }

        $product =  Product::with(['category', 'galleries']);

        if ($name) {
            $product->where('name', 'like', '%' . $name . '%');
        }
        if ($desription) {
            $product->where('name', 'like', '%' . $desription . '%');
        }
        if ($tags) {
            $product->where('name', 'like', '%' . $tags . '%');
        }
        if ($price_from) {
            $product->where('price', '>=', $price_from);
        }
        if ($price_to) {
            $product->where('price', '<=', $price_to);
        }
        if ($categories) {
            $product->where('categories', '$categories');
        }

        return ResponseFormatter::success(
            $product->paginate($limit),
            'Data produk berhasil di ambil'
        );
    }

    public function create(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'categories_id' => 'required|exists:product_categories,id',
                'tags' => 'nullable|string',
                // Add validation rules for other fields if necessary
                'gallery.*' => 'image|mimes:jpg,png,jpeg|max:2048', // Validation rule for gallery images
            ]);

            // Create Product
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'categories_id' => $request->categories_id,
                'tags' => $request->tags,
                // Add other fields if necessary
            ]);

            // Create ProductGallery
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $image) {
                    $url = $image->store('gallery', 'public'); // Adjust storage folder as needed
                    ProductGallery::create([
                        'products_id' => $product->id,
                        'url' => $url,
                    ]);
                }
            }

            // Optionally, you can attach ProductCategory if needed
            $category = ProductCategory::find($request->categories_id);
            if ($category) {
                $product->category()->associate($category);
                $product->save();
            }

            return ResponseFormatter::success(
                $product,
                'Produk berhasil dibuat beserta galeri'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                'Terjadi kesalahan saat membuat produk beserta galeri',
                500
            );
        }
    }

    public function detail($id)
    {
        try {
            $product = Product::with(['category', 'galleries'])->find($id);

            if (!$product) {
                return ResponseFormatter::error(
                    null,
                    'Produk tidak ditemukan',
                    404
                );
            }

            return ResponseFormatter::success(
                $product,
                'Data produk berhasil diambil'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                'Terjadi kesalahan saat mengambil detail produk',
                500
            );
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return ResponseFormatter::error(
                    null,
                    'Produk tidak ditemukan',
                    404
                );
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric',
                'categories_id' => 'required|exists:product_categories,id',
                'tags' => 'nullable|string',
                // Add validation rules for other fields if necessary
                'gallery.*' => 'image|mimes:jpg,png,jpeg|max:2048', // Validation rule for gallery images
            ]);

            // Update Product
            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'categories_id' => $request->categories_id,
                'tags' => $request->tags,
                // Add other fields if necessary
            ]);

            foreach ($product->galleries as $gallery) {
                // Storage::disk('public')->delete($gallery->url);
                $gallery->delete(); // Delete records from ProductGallery table
            }

            $galleryFiles = $request->file('gallery');

            if (is_array($galleryFiles)) {
                foreach ($galleryFiles as $image) {
                    $url = $image->store('gallery', 'public'); // Adjust storage folder as needed
                    ProductGallery::create([
                        'products_id' => $product->id,
                        'url' => $url,
                    ]);
                }
            } else {
                // Single file upload scenario
                $url = $galleryFiles->store('gallery', 'public');
                ProductGallery::create([
                    'products_id' => $product->id,
                    'url' => $url,
                ]);
            }

            // Optionally, you can update ProductCategory if needed
            $category = ProductCategory::find($request->categories_id);
            if ($category) {
                $product->category()->associate($category);
                $product->save();
            }

            return ResponseFormatter::success(
                $product,
                'Produk berhasil diperbarui beserta galeri'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                'Terjadi kesalahan saat memperbarui produk beserta galeri',
                500
            );
        }
    }

    public function delete($id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return ResponseFormatter::error(
                    null,
                    'Produk tidak ditemukan',
                    404
                );
            }

            // Delete associated images from storage
            foreach ($product->galleries as $gallery) {
                Storage::disk('public')->delete($gallery->url);
                $gallery->delete();
            }

            $product->delete();

            return ResponseFormatter::success(
                null,
                'Produk berhasil dihapus beserta galerinya'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                'Terjadi kesalahan saat menghapus produk beserta galeri',
                500
            );
        }
    }

}
