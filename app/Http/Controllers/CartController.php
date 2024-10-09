<?php

namespace App\Http\Controllers;


use App\Models\Cart;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CartController extends Controller
{
    public function getCarts()
    {
        $user = JWTAuth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $carts = Cart::where('user_id', $user->id)->with('product')->get();
        return response()->json($carts);
    }

    public function createCart(Request $request)
    {
        $user = JWTAuth::user();
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $cart = Cart::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($cart) {
            $cart->quantity += 1;
            $cart->save();
        } else {
            $cart = new Cart();
            $cart->user_id = $user->id;
            $cart->product_id = $request->product_id;
            $cart->quantity = 1;
            $cart->save();
        }

        return response()->json(['message' => 'Product add to Cart'], 201);
    }

    public function updateCart(Request $request, $id)
    {
        $user = JWTAuth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $cart = Cart::where('user_id', $user->id)->find($id);
        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        $request->validate([
            'quantity' => 'required|integer',
        ]);

        $cart->quantity += $request->quantity;

        if ($cart->quantity < 1) {
            $cart->delete();
            return response()->json(['message' => 'Cart item removed']);
        } else {
            $cart->save();
            return response()->json(['message' => 'Cart updated successfully']);
        }
    }


    public function destroyCart($id)
    {
        $user = JWTAuth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $cart = Cart::where('user_id', $user->id)->find($id);
        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        $cart->delete();

        return response()->json(['message' => 'Cart deleted successfully']);
    }
}
