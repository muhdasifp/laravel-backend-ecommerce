<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function getProducts()
    {
        // $products = Product::with('category', 'ratings');
        // foreach ($products as $product) {
        //     $product->average_rating = $product->ratings->avg('rating');
        //     $product->rating_count = $product->ratings->count();
        // }
        // return response()->json($products);
        $products = Product::all()->map(function ($product) {
            $product->rating = $product->averageRating();
            return $product;
        });
        return response()->json($products, 200);
    }

    public function createProduct(Request $request)
    {
        $request->validate([
            'title' => 'required|string|unique:products,title',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'stock' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
        ]);

        $productName = time() . '.' . $request->file('image')->getClientOriginalExtension();
        $request->file('image')->move('assets/upload/product', $productName);
        $path = url('assets/upload/product/' . $productName);

        $product = Product::create([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $path,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
        ]);

        return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
    }

    public function getProduct($id)
    {
        $product = Product::with('ratings.user')->find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        $product->rating = $product->averageRating();
        return response()->json($product, 200);
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
        $imagePath = public_path('assets/upload/product/' . basename($product->image));
        File::delete($imagePath);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }

    public function getProductByCategory($id)
    {
        $products = Product::where('category_id', $id)->get();
        return response()->json($products, 200);
    }
}
