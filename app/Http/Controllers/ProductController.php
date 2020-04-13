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

        return $this->user()->products()->paginate($per_page);
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
            'type' => 'required|string|in:art,idea,memory',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|string|max:255',
            'on_sale' => 'required|boolean',
            'price' => 'required|numeric',
        ]);

        Product::create([
            'user_id' => $this->userId(),
            'type' => $request->input('type'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'image' => $request->input('image'),
            'on_sale' => $request->input('on_sale'),
            'price' => $request->input('price'),
        ]);

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
        return $this->user()->products()->findOrFail($id);
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
        $product = $this->user()->products()->findOrFail($id);

        $product->update($request->only([
            'type',
            'title',
            'description',
            'image',
            'on_sale',
            'price',
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
