<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrderController extends Controller
{
    public function buyIndex(Request $request)
    {
        $request->validate([
            'page' => 'integer',
            'per_page' => 'integer',
        ]);

        $per_page = 10;

        if ($request->has('per_page')) {
            $per_page = $request->input('per_page');
        }

        return $this->user()->buyOrders()->paginate($per_page);
    }

    public function sellIndex(Request $request)
    {
        $request->validate([
            'page' => 'integer',
            'per_page' => 'integer',
        ]);

        $per_page = 10;

        if ($request->has('per_page')) {
            $per_page = $request->input('per_page');
        }

        return $this->user()->sellOrders()->paginate($per_page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'required|email',
            'company' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'postcode' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        $product = Product::findOrFail($request->input('product_id'));
        if($product['sale_way'] !== Product::SALE_WAY_DIRECT) {
            throw new BadRequestHttpException('Cannot create new order, the sale way of product is not direct');
        }
        $orderArray = $request->only([
            'sale_way',
        ]);
        $orderArray['total_amount'] = $product['price'];
        $order = $this->user()->buyOrders()->create($orderArray);

        $shippingArray = $request->only([
            'first_name',
            'last_name',
            'phone',
            'email',
            'company',
            'country',
            'province',
            'city',
            'street',
            'postcode',
        ]);
        $shippingArray['seller_id'] = $product['user_id'];
        $shippingArray['status'] = Shipping::STATUS_PENDING;
        $order->shipping()->create($shippingArray);

        $orderItemArray = [
            'product_id' => $product['id'],
            'price' => $product['price'],
            'amount' => $product['price'],
            'count' => 1,
        ];
        $order->items()->create($orderItemArray);
        DB::commit();

        return new Response('', 201);
    }

    public function showBuyOrder($id)
    {
        return $this->user()
            ->buyOrders()
//            ->with('orderItems')
//            ->with('orderPays')
//            ->with('orderRefunds')
            ->findOrFail($id);
    }

    public function showSellOrder($id)
    {
        return $this->user()
            ->sellOrders()
//            ->with('orderItems')
//            ->with('orderPays')
//            ->with('orderRefunds')
            ->findOrFail($id);
    }

    /**
     * 订单支付(买家)
     *
     * @param $id
     */
    public function pay($id)
    {
        DB::beginTransaction();
        $order = $this->user()->buyOrders()->findOrFail($id);
        if($order['status'] !== Order::PAY_STATUS_PENDING) {
            throw new BadRequestHttpException('Cannot pay for the order, the status of this order is not pending.');
        }
        // transfer from buyer to seller
//        $toId = $order['seller_id'];
        $amount = $order['total_amount'];
        $this->user()->waller()->lock($amount);
//        $this->user()->transfer($toId, $amount);
        // update order's status
        $order->update([
            'status' => Order::PAY_STATUS_PAID,
        ]);
        DB::commit();
    }

    /**
     * 订单发货(卖家)
     *
     * @param Request $request
     * @param $id
     */
    public function shipping(Request $request, $id)
    {
        DB::beginTransaction();
        $order = $this->user()->sellOrders()->findOrFail($id);
        if($order['status'] !== Order::PAY_STATUS_PAID) {
            throw new BadRequestHttpException('Cannot shipping for the order, the status of this order is not paid.');
        }
        $order->shipping()->update([
            'status' => Shipping::STATUS_DELIVERED
        ]);
        DB::commit();

        return new Response('', 200);
    }

    /**
     * 订单确认(买家)
     *
     * @param $id
     */
    public function confirm($id)
    {
        DB::beginTransaction();
        // 获取订单
        $order = $this->user()->buyOrders()->findOrFail($id);
        // 转账
        $toId = $order['seller_id'];
        $amount = $order['total_amount'];
        $this->user()->unlockAndTransfer($toId, $amount);
        // 更新物流状态
        $order->shipping->update([
            'status' => Shipping::STATUS_RECEIVED,
        ]);
        // 更新订单状态
        $order->update([
            'status' => Order::PAY_STATUS_COMPLETE,
        ]);
        DB::commit();

        return new Response('', 200);
    }
}
