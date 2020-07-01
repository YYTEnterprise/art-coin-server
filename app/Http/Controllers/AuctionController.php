<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AuctionController extends Controller
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

        return $this->user()->auctions()->paginate($per_page);
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
            'start_price' => 'required|numeric',
            'step_price' => 'required|numeric',
            'fixed_price' => 'numeric',
            'start_at' => 'required|date',
            'end_at' => 'required|date',
        ]);

        $product = Product::findOrFail($request->input('product_id'));
        if($product['sale_way'] !== Product::SALE_WAY_AUCTION) {
            throw new BadRequestHttpException('Cannot create new auction, the sale way of product is not auction');
        }

        $auction = Auction::where('product_id', $request->input('product_id'))
            ->where('status', Auction::STATUS_BIDDING)
            ->first();
        if($auction) {
            throw new BadRequestHttpException('Cannot create new auction, an existed auction has not finished yet');
        }

        $auction = Auction::create($request->only([
            'product_id',
            'start_price',
            'step_price',
            'fixed_price',
            'start_at',
            'end_at',
        ]));

        return $auction;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $auction = $this->user()
            ->auctions()
            ->with('product')
            ->findOrFail($id);

        $sellerId = $auction['product']['user_id'];
        $seller = User::find($sellerId);
        $auction['seller'] = $seller;

        return $auction;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'start_price' => 'numeric',
            'step_price' => 'numeric',
            'fixed_price' => 'numeric',
            'start_at' => 'date',
            'end_at' => 'date',
        ]);

        $auction = $this->user()->auctions()->findOrFail($id);

        // 如果有正在进行的拍卖，不允许更新
        if (strtotime($auction['start_at']) <= time() ) {
            throw new BadRequestHttpException('The bid is start');
        }

        $auction->update($request->only([
            'start_price',
            'step_price',
            'fixed_price',
            'start_at',
            'end_at',
        ]));

        return $auction;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $auction = $this->user()->auctions()->findOrFail($id);

        // 如果有正在进行的拍卖，不允许删除
        if (strtotime($auction['start_at']) <= time() ) {
            throw new BadRequestHttpException('The bid is start');
        }

        $auction->delete();

        return new Response('', 200);
    }

    /**
     * 开始竞拍(卖家触发)
     *
     * @param $id
     * @return Response
     */
    public function startBid($id)
    {
        $auction = $this->user()->auctions()->findOrFail($id);

        if (strtotime($auction['start_at']) > time() ) {
            throw new BadRequestHttpException('The bid is not start');
        }
        if ($auction['end_at'] <= now() ) {
            throw new BadRequestHttpException('The bid is expired');
        }

        // check auction
        if ($auction['status'] !== Auction::STATUS_INITIAL) {
            throw new BadRequestHttpException('The bid status is not initial');
        }

        $auction->update([
            'status' => Auction::STATUS_BIDDING,
        ]);

        return new Response('', 200);
    }

    /**
     * 竞拍(买家触发)
     *
     * @param $id
     * @return Response
     */
    public function bid(Request $request, $id)
    {
        $request->validate([
            'bid_price' => 'required|numeric',
            'password' => 'string',
        ]);

        // check payment password
        $user = $this->user();
        $password = $request->input('password');
        if (empty($user->pay_passwd)) {
            throw new BadRequestHttpException('The payement password is not set, please set the password first.');
        } else {
            if(!Hash::check($password, $user->pay_passwd)) {
                throw new BadRequestHttpException('The payment password is not correct.');
            }
        }

        $bidPrice = $request->input('bid_price');
        $userId = $this->userId();
        $auction = Auction::findOrFail($id);

        if (strtotime($auction['start_at']) > time() ) {
            throw new BadRequestHttpException('The bid is not start');
        }
        if (strtotime($auction['end_at']) <= time() ) {
            throw new BadRequestHttpException('The bid is expired');
        }

        // check auction
        if ($auction['status'] !== Auction::STATUS_BIDDING) {
            throw new BadRequestHttpException('The bid status is not bidding');
        }
        if (floatval($auction['start_price']) > floatval($bidPrice)) {
            throw new BadRequestHttpException('The bid price can\'t be lower than start price');
        }
        if (floatval($auction['current_price']) + floatval($auction['step_price']) > floatval($bidPrice)) {
            throw new BadRequestHttpException('The bid price is too low');
        }

        $auction->newBid($userId, $bidPrice);

        return new Response('', 200);
    }

    /**
     * 一口价(买家触发)
     *
     * @param $id
     * @return Response
     */
    public function fixed(Request $request, $id)
    {
        $request->validate([
            'password' => 'string',
        ]);

        DB::beginTransaction();

        // check payment password
        $user = $this->user();
        $password = $request->input('password');
        if (empty($user->pay_passwd)) {
            throw new BadRequestHttpException('The payement password is not set, please set the password first.');
        } else {
            if(!Hash::check($password, $user->pay_passwd)) {
                throw new BadRequestHttpException('The payment password is not correct.');
            }
        }

        $userId = $this->userId();
        $auction = Auction::findOrFail($id);

        if (strtotime($auction['start_at']) > time() ) {
            throw new BadRequestHttpException('The bid is not start');
        }
        if (strtotime($auction['end_at']) <= time() ) {
            throw new BadRequestHttpException('The bid is expired');
        }

        // check auction
        if ($auction['status'] !== Auction::STATUS_BIDDING) {
            throw new BadRequestHttpException('The bid status is not bidding');
        }
        $auction->newFixed($userId, $auction['fixed_price']);

        // 创建订单
        $product = Product::findOrFail($auction['product_id']);
        $amount = $auction['fixed_price'];
        Order::new($this->user(), $product, $amount);

        DB::commit();

        return new Response('', 200);
    }
}
