<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CartController extends Controller
{

    public function show()
    {
        return $this->user()->cartItems;
    }

    /**
     * 向购物车添加物品
     * @param Request $request
     * @param $id
     */
    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $productId = $request->input('product_id');
        $product = Product::findOrFail($productId);
        if($product['sale_way'] !== Product::SALE_WAY_DIRECT) {
            throw new BadRequestHttpException('Cannot add this product to cart, the sale way of product is not direct');
        }

        $this->user()->cartItems()->create([
            'product_id' => $productId,
            'price' => $product['price'],
            'amount' => $product['price'],
            'count' => 1,
        ]);

        return $this->user()->cartItems;
    }

    /**
     * 向购物车移除物品
     * @param Request $request
     * @param $id
     */
    public function removeItem(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|integer|exists:cart_items,id',
        ]);

        $cartItemId = $request->input('cart_item_id');
        $this->user()->cartItems()->delete($cartItemId);

        return $this->user()->cartItems;
    }

    public function removeAll()
    {
        $this->user()->cartItems()->delete();
    }

    /**
     * 更新购物车物品数量
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateItem(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|integer|exists:cart_items,id',
            'product_count' => 'required|integer|min:1',
        ]);

        $cartItemId = $request->input('cart_item_id');
        $productCount = $request->input('product_count');
        $item = $this->user()->cartItems()->findOrFail($cartItemId);
        $item->update([
           'count' =>  $productCount
        ]);

        return $this->user()->cartItems;
    }
}
