<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;


class CategoryController extends Controller
{
    public function allCatory()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function saveCategory(Request $request)
    {
        $request->validate([
            'title' => 'required|string|unique:categories,title',
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);

        $categoryname = time() . '.' . $request->file('image')->getClientOriginalExtension();
        $request->file('image')->move('assets/upload/category', $categoryname);
        $imageURL = url('assets/upload/category/' . $categoryname);

        $category = new Category();
        $category->title = $request->title;
        $category->image = $imageURL;
        $category->save();

        return response()->json(['message' => 'Category created successfully']);
    }

    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }
        return response()->json($category);
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }
        $imageURL = public_path('assets/upload/category/' . basename($category->image));

        File::delete($imageURL);

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully'], 200);
    }

    public function getCategoryProduct($category)
    {
        $products = Category::with('products')->where('title', $category)->first();
        return response()->json($products, 200);
    }
}
