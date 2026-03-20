<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // List all products
    public function index()
    {
        $products = Product::all();

        $products->transform(function ($product) {
            $product->image = $product->image ? asset('storage/' . $product->image) : null;
            return $product;
        });

        return response()->json($products);
    }

    // Show single product
    public function show(Product $product)
    {
        $product->image = $product->image ? asset('storage/' . $product->image) : null;
        return response()->json($product);
    }

    // Store new product
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'category' => 'required|string',
            'image' => 'nullable|image',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images', 'public');
            $data['image'] = $path;
        }

        $product = Product::create($data);
        $product->image = $product->image ? asset('storage/' . $product->image) : null;

        return response()->json($product, 201);
    }

    // Update product
    public function update(Request $request, Product $product)
    {

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric',
            'category' => 'sometimes|required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('images', 'public');
            $data['image'] = $path;
        }

        $product->update($data);

        $product->image = $product->image ? asset('storage/' . $product->image) : null;

        return response()->json($product);
    }

    // Delete product
    public function destroy(Product $product)
    {
        
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }
}
