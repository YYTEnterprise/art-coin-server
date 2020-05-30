<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Product[]|\Illuminate\Database\Eloquent\Collection
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

        return $this->user()
            ->products()
            ->withCount('likes')
            ->with('auction')
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
            'title' => 'required|string|max:255',
            'brief_desc' => 'required|string',
            'detail_desc' => 'required|string',
            'cover_image' => 'required|string|max:255',
            'price' => 'required|numeric',
            'deliver_type' => 'required|string|in:express,email',
            'has_deliver_fee' => 'required_if:deliver_type,express|boolean',
            'has_tariff' => 'required_if:deliver_type,express|boolean',
//            'deliver_remark' => 'required_if:deliver_type,email|string',
            'on_sale' => 'boolean',
            'sale_way' => 'required|string|in:direct,auction',
        ]);

        $this->user()->products()->create($request->only([
            'title',
            'brief_desc',
            'detail_desc',
            'cover_image',
            'price',
            'deliver_type',
            'has_deliver_fee',
            'has_tariff',
//            'deliver_remark',
            'on_sale',
            'sale_way',
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
        return $this->user()
            ->products()
            ->withCount('likes')
            ->with('auction')
            ->findOrFail($id);
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
            'title' => 'string|max:255',
            'brief_desc' => 'string',
            'detail_desc' => 'string',
            'cover_image' => 'string|max:255',
            'price' => 'numeric',
            'deliver_type' => 'string|in:express,email',
            'has_deliver_fee' => 'required_if:deliver_type,express|boolean',
            'has_tariff' => 'required_if:deliver_type,express|boolean',
            'deliver_remark' => 'required_if:deliver_type,email|string',
        ]);

        $product = $this->user()->products()->findOrFail($id);

        $product->update($request->only([
            'title',
            'brief_desc',
            'detail_desc',
            'cover_image',
            'price',
            'deliver_type',
            'has_deliver_fee',
            'has_tariff',
            'deliver_remark',
        ]));

        return $product;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->user()->products()->findOrFail($id)->delete();

        return new Response('', 200);
    }

    public function onsale($id)
    {
        $this->user()->products()->findOrFail($id)->update(['on_sale' => true]);

        return new Response('', 200);
    }

    public function offsale($id)
    {
        $this->user()->products()->findOrFail($id)->update(['on_sale' => false]);

        return new Response('', 200);
    }
}
