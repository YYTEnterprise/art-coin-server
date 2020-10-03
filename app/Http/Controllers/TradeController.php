<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Models\TradeInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TradeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
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

        return Trade::with('tradeInfo')
            ->with('buyer')
            ->orderBy('updated_at', 'desc')
            ->paginate($per_page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'trade_info_id' => 'required|integer|exists:trade_infos,id',
            'amount' => 'required|numeric',
            'usd_amount' => 'required|numeric',
        ]);

        $tradeInfo = TradeInfo::findOrFail($request->input('trade_info_id'));
        $trader = $tradeInfo->trader;

        $params = $request->only([
            'trade_info_id',
            'amount',
            'usd_amount',
        ]);
        $params['price'] = $tradeInfo['price'];
        $params['trade_type'] = $trader['trade_type'];
        $params['trade_account'] = $trader['trade_account'];

        return $this->user()->trades()->create($params);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function show($id)
    {
        return Trade::with('tradeInfo')
            ->with('buyer')
            ->findOrFail($id);
    }

    public function pay($id)
    {
        $trade = $this->user()->trades()->findOrFail($id);
        if ($trade['status'] != Trade::STATUS_PENDING) {
            throw new BadRequestHttpException('Cannot pay for the trade, the status of this trade is not pending.');
        }
        $trade['status'] = Trade::STATUS_PAID;
        $trade->save();

        return $trade;
    }

    public function confirm($id)
    {
        $trade = Trade::findOrFail($id);
        if ($trade['status'] != Trade::STATUS_PAID) {
            throw new BadRequestHttpException('Cannot confirm the trade, the status of this trade is not paid.');
        }
        $trade['status'] = Trade::STATUS_COMPLETE;
        $trade->save();

        // TODO 给用户发行 token

        return $trade;
    }

    public function cancel($id)
    {
        $trade = $this->user()->trades()->findOrFail($id);
        if ($trade['status'] != Trade::STATUS_PENDING) {
            throw new BadRequestHttpException('Cannot pay for the trade, the status of this trade is not pending.');
        }
        $trade['status'] = Trade::STATUS_CANCEL;
        $trade->save();

        return $trade;
    }
}
