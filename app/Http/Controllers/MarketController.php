<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class MarketController extends Controller
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
            'sale_way' => 'string|in:direct,auction',
        ]);

        $per_page = 10;

        if ($request->has('per_page')) {
            $per_page = $request->input('per_page');
        }

        if  ($request->has('sale_way')) {
            return Product::where('on_sale', true)
                ->where('sale_way', $request->input('sale_way'))
                ->withCount('likes')
                ->with('auction')
                ->orderBy('updated_at', 'desc')
                ->paginate($per_page);
        } else {
            return Product::where('on_sale', true)
                ->withCount('likes')
                ->with('auction')
                ->orderBy('updated_at', 'desc')
                ->paginate($per_page);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Product::where('on_sale', true)
            ->withCount('likes')
            ->with('auction')
            ->with('user')
            ->findOrFail($id);
    }
}
