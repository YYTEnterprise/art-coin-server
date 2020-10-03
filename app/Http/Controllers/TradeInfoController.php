<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Models\TradeInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TradeInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return TradeInfo[]|\Illuminate\Database\Eloquent\Collection
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

        return TradeInfo::with('trader')
            ->orderBy('updated_at', 'desc')
            ->paginate($per_page);
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
            'trader_id' => 'required|integer',
            'max_amount' => 'required|numeric',
            'max_usd_amount' => 'required|numeric',
            'min_usd_amount' => 'required|numeric',
            'price' => 'required|numeric',
            'trade_type' => 'required|string|in:paypal',
        ]);

        $tradeInfo = TradeInfo::create($request->only([
            'trader_id',
            'max_amount',
            'max_usd_amount',
            'min_usd_amount',
            'price',
            'trade_type',
        ]));

        return $tradeInfo;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return TradeInfo::with('trader')->findOrFail($id);
    }
}
