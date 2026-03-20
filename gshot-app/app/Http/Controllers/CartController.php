<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Fetch all cart items for the logged-in user
    public function index()
    {
        $cart = Cart::with('product')
            ->where('user_id', Auth::id())
            ->get();

        return response()->json($cart); 
    }

    // Add a product to the cart
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = Cart::firstOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $request->product_id],
            ['quantity' => 0]
        );

        $cartItem->quantity += $request->quantity;
        $cartItem->save();

        return $this->index();
    }

    // Update quantity of a product in the cart
    public function update(Request $request, $product_id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = Cart::where('user_id', Auth::id())
            ->where('product_id', $product_id)
            ->firstOrFail();

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return $this->index();
    }

    // Remove a product from the cart
    public function destroy($product_id)
    {
        Cart::where('user_id', Auth::id())
            ->where('product_id', $product_id)
            ->delete();

        return $this->index();
    }

    // Clear the entire cart
    public function clear()
    {
        Cart::where('user_id', Auth::id())->delete();

        return response()->json([]);
    }
}