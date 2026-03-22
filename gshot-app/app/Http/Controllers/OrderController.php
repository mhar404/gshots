<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // ✅ Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'payment' => 'required|string|max:50',
            'address' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'total' => 'required|numeric|min:0',
        ]);

        // ✅ Create the order
        $order = Order::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'payment' => $request->payment,
            'address' => $request->address,
            'total' => $request->total,
        ]);

        // ✅ Create the order items
        foreach ($request->items as $item) {
            $order->items()->create([
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        // ✅ Return response
        return response()->json([
            'message' => 'Order created successfully',
            'order_id' => $order->id
        ]);
    }

    // Optional: view all orders (for admin)
    public function index()
    {
        $orders = Order::with('items.product')->latest()->get();
        return response()->json($orders);
    }
}
