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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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

        return $this->user()->orders()->paginate($per_page);
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
            'product_id' => 'integer|exists:products,id',
            'sale_way' => 'string|in:direct,auction',
        ]);

        DB::beginTransaction();
        $product = Product::findOrFail($request->input('product_id'));
        if($product['sale_way'] !== Product::SALE_WAY_DIRECT) {
            throw new BadRequestHttpException('Cannot create new auction, the sale way of product is not direct');
        }
        $orderArray = $request->only([
            'sale_way',
        ]);
        $orderArray['total_amount'] = $product['price'];
        $order = $this->user()->orders()->create($orderArray);

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
        $shippingArray['status'] = Shipping::STATUS_PENDING;
        $order->shipping()->create($shippingArray);

        $orderItemArray = [
            'product_id' => $product['id'],
            'price' => $product['price'],
            'amount' => $product['price'],
            'count' => 1,
        ];
        $order->orderItems()->create($orderItemArray);
        DB::commit();

        return new Response('', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return $this->user()
            ->orders()
            ->with('orderItems')
            ->with('orderPays')
            ->with('orderRefunds')
            ->findOrFail($id);
    }

    /**
     * 订单支付
     *
     * @param $id
     */
    public function pay($id)
    {
        $order = $this->user()->orders()->findOrFail($id);

        DB::beginTransaction();
        $order->update([
            'status' => Order::PAY_STATUS_PAID,
        ]);
        $toUserId = $order['user_id'];
        $amount = $order['total_amount'];
        $this->user()->wallet->transfer($toUserId, $amount);
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
        // 获取订单
        $order = Order::findOrFail($id);

        $order->shipping()->update([
            'status' => Shipping::STATUS_DELIVERED
        ]);

        return new Response('', 200);
    }

    /**
     * 订单确认(买家)
     *
     * @param $id
     */
    public function confirm($id)
    {
        // 获取订单
        $order = $this->user()->orders()->findOrFail($id);

        DB::beginTransaction();
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
