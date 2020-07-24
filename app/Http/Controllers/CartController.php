<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'page' => 'integer',
            'per_page' => 'integer',
        ]);

        $per_page = 10;

        if ($request->has('per_page')) {
            $per_page = $request->input('per_page');
        }

        return $this->user()
            ->carts()
            ->with('user')
            ->with('items')
            ->paginate($per_page);
    }

    public function store(Request $request)
    {
        $cart = $this->user()->carts()->create();

        return Cart::with('user')
            ->with('items')
            ->findOrFail($cart['id']);
    }

    public function show($id)
    {
        return $this->user()
            ->carts()
            ->with('user')
            ->with('items')
            ->findOrFail($id);
    }

    /**
     * 向购物车添加物品
     * @param Request $request
     * @param $id
     */
    public function addItem(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
//            'product_count' => 'required|integer|min:1',
        ]);
        $cart = $this->user()->carts()->findOrFail($id);
        if ($cart['status'] !== Cart::CART_STATUS_PENDING) {
            throw new BadRequestHttpException('Cannot add item to cart, the cart status is not pending');
        }

        $productId = $request->input('product_id');
        $product = Product::findOrFail($productId);
        if($product['sale_way'] !== Product::SALE_WAY_DIRECT) {
            throw new BadRequestHttpException('Cannot add this product to cart, the sale way of product is not direct');
        }

        $cart->items()->create([
            'product_id' => $productId,
            'price' => $product['price'],
            'amount' => $product['price'],
//            'count' => $request->input('product_count'),
            'count' => 1,
        ]);

        return $this->user()
            ->carts()
            ->with('user')
            ->with('items')
            ->findOrFail($id);
    }

    /**
     * 向购物车移除物品
     * @param Request $request
     * @param $id
     */
    public function removeItem(Request $request, $id)
    {
        $request->validate([
            'cart_item_id' => 'required|integer|exists:cart_items,id',
        ]);
        $cart = $this->user()->carts()->findOrFail($id);
        if ($cart['status'] !== Cart::CART_STATUS_PENDING) {
            throw new BadRequestHttpException('Cannot add item to cart, the cart status is not pending');
        }

        $cartItemId = $request->input('cart_item_id');
        $cart = $this->user()->carts()->findOrFail($id);
        $cart->items()->delete($cartItemId);

        return $this->user()
            ->carts()
            ->with('user')
            ->with('items')
            ->findOrFail($id);
    }

    /**
     * 更新购物车物品数量
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateItem(Request $request, $id)
    {
        $request->validate([
            'cart_item_id' => 'required|integer|exists:cart_items,id',
            'product_count' => 'required|integer|min:1',
        ]);
        $cart = $this->user()->carts()->findOrFail($id);
        if ($cart['status'] !== Cart::CART_STATUS_PENDING) {
            throw new BadRequestHttpException('Cannot add item to cart, the cart status is not pending');
        }

        $cartItemId = $request->input('cart_item_id');
        $productCount = $request->input('product_count');
        $cart = $this->user()->carts()->findOrFail($id);
        $item = $cart->items()->findOrFail($cartItemId);
        $item->update([
           'count' =>  $productCount
        ]);

        return $this->user()
            ->carts()
            ->with('user')
            ->with('items')
            ->findOrFail($id);
    }
}
