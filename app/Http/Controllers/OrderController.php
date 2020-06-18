<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Shipping;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

        $product = Product::findOrFail($request->input('product_id'));
        if($product['sale_way'] !== Product::SALE_WAY_DIRECT) {
            throw new BadRequestHttpException('Cannot create new order, the sale way of product is not direct');
        }
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
        $order = Order::new($this->user(), $product, $product['price'], $shippingArray);

        return $order;
    }

    public function showBuyOrder($id)
    {
        return $this->user()
            ->buyOrders()
            ->with('items')
            ->with('shipping')
            ->with('pays')
            ->with('refunds')
            ->findOrFail($id);
    }

    public function showSellOrder($id)
    {
        return $this->user()
            ->sellOrders()
            ->with('items')
            ->with('shipping')
            ->with('pays')
            ->with('refunds')
            ->findOrFail($id);
    }

    /**
     * 订单支付(买家)
     *
     * @param $id
     */
    public function pay(Request $request, $id)
    {
        $request->validate([
            'password' => 'string',
        ]);
        $user = $this->user();
        $password = $request->input('password');

        DB::beginTransaction();
        if (empty($user->pay_passwd)) {
            throw new BadRequestHttpException('The payement password is not set, please set the password first.');
        } else {
            if(!Hash::check($password, $user->pay_passwd)) {
                throw new BadRequestHttpException('The payment password is not correct.');
            }
        }
        $order = $user->buyOrders()->findOrFail($id);
        if($order['status'] !== Order::PAY_STATUS_PENDING) {
            throw new BadRequestHttpException('Cannot pay for the order, the status of this order is not pending.');
        }
        // lock amount from buyer
        $amount = $order['total_amount'];
        $user->wallet->lock($amount);
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
        if($order->shipping['status'] !== Shipping::STATUS_PENDING) {
            throw new BadRequestHttpException('Cannot shipping for the order, the status of this shipping is not pending.');
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
        if($order['status'] !== Order::PAY_STATUS_PAID) {
            throw new BadRequestHttpException('Cannot confirm for the order, the status of this order is not paid.');
        }
        if($order->shipping['status'] !== Shipping::STATUS_DELIVERED) {
            throw new BadRequestHttpException('Cannot confirm for the order, the status of this shipping is not delivered.');
        }
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
