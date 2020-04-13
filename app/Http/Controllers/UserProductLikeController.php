<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserProductLikeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function likeList($id)
    {
        return Product::findOrFail($id)->likes;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function like($id)
    {
        $userId = $this->userId();

        $product = Product::findOrFail($id);

        // check if record exists
        if ($product->likes()->wherePivot('user_id', $userId)->first()) {
            return new Response('', 200);
        }

        $product->likes()->attach($userId);

        return new Response('', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function unlike($id)
    {
        $userId = $this->userId();

        $product = Product::where('user_id', $userId)->findOrFail($id);

        // check if record not exists
        if (!$product->likes()->wherePivot('user_id', $userId)->first()) {
            return new Response('', 200);
        }

        if ($product->likes()->detach($userId)) {
            return new Response('', 200);
        }

        throw new BadRequestHttpException('unlike failed');
    }
}
