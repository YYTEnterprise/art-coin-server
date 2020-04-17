<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            'product_id' => 'integer|exists:products,id',
            'start_price' => 'required|numeric',
            'step_price' => 'required|numeric',
            'fixed_price' => 'numeric',
            'start_at' => 'required|date',
            'end_at' => 'required|date',
        ]);

        $auction = Auction::where('product_id', $request->input('product_id'))
            ->whereIn('status', ['initial', 'bidding'])
            ->first();
        if($auction) {
            throw new BadRequestHttpException('Cannot create new auction, an existed auction has not finished yet');
        }

        Auction::create($request->only([
            'product_id',
            'start_price',
            'step_price',
            'fixed_price',
            'start_at',
            'end_at',
        ]));

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
        return $this->user()->auctions()->findOrFail($id);

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
        $this->user()->auctions()->findOrFail($id)->delete();

        return new Response('', 200);
    }
}
