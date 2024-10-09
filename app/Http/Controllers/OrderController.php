<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;


class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $user = JWTAuth::user();

        // Create the new order
        $order = Order::create([
            'user_id' => $user->id,
            'total' => 0, // Will calculate total later
            'status' => 'pending'
        ]);

        $cartItems = Cart::where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        $total = 0;

        foreach ($cartItems as $item) {
            $product = Product::find($item->product_id);

            // Check if enough stock is available
            if ($product->stock < $item->quantity) {
                return response()->json([
                    'error' => 'Not enough stock for product: ' . $product->name
                ], 400);
            }

            // Create order item from the cart item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $product->price
            ]);

            // Reduce product stock
            $product->stock -= $item->quantity;
            $product->save();

            // Calculate the total order price
            $total += $product->price * $item->quantity;
        }
        // Set the total price of the order
        $order->total = $total;
        $order->save();

        // Delete all cart items
        Cart::where('user_id', $user->id)->delete();

        return response()->json(['message' => 'Ordered Successfully'], 201);
    }

    public function getOrders()
    {
        $user = JWTAuth::user();

        $orders = Order::with('items.product')->where('user_id', $user->id)->get();
        return response()->json($orders);
    }

    public function getSingleOrder($id)
    {
        $user = JWTAuth::user();

        $order = Order::with('items.product')->where('id', $id)->where('user_id', $user->id)->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json($order, 200);
    }

    public function cancelOrder($id)
    {
        $user = JWTAuth::user();

        $order = Order::with('items.product')->where('id', $id)->where('user_id', $user->id)->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Check if the order can be canceled
        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Order cannot be canceled'], 400);
        }

        // Restore stock for each product
        foreach ($order->items as $orderItem) {
            $product = $orderItem->product;
            $product->stock += $orderItem->quantity; // Restore stock
            $product->save();
        }

        // Set the order status to 'canceled'
        $order->status = 'canceled';
        $order->save();

        return response()->json(['message' => 'Order canceled successfully'], 200);
    }

    // public function createOrder(Request $request)
    // {
    //     $user = JWTAuth::user();
    //     if (!$user) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $validatedData = $request->validate([
    //         'products' => 'required|array',
    //         'products.*.id' => 'required|exists:products,id',
    //         'products.*.quantity' => 'required|integer',
    //     ]);

    //     $order = new Order();
    //     $order->user_id = $user->id;
    //     $order->total = 0;
    //     $order->status = 'pending';
    //     $order->save();

    //     foreach ($validatedData['products'] as $productData) {
    //         $product = Product::find($productData['id']);
    //         $orderItem = new OrderItem();
    //         $orderItem->order_id = $order->id;
    //         $orderItem->product_id = $product->id;
    //         $orderItem->quantity = $productData['quantity'];
    //         $orderItem->price = $product->price;
    //         $orderItem->save();

    //         $order->total += $product->price * $productData['quantity'];
    //     }

    //     $order->save();

    //     return response()->json(['message' => 'Order created successfully']);
    // }
}
