<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserFollowController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function followerList()
    {
        return $this->user()->followers;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function followingList()
    {
        return $this->user()->followings;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $followUserId
     * @return \Illuminate\Http\Response
     */
    public function follow(Request $request)
    {
        $request->validate([
            'follow_user_id' => 'required|int|exists:users,id',
        ]);

        $followUserId = $request->input('follow_user_id');

        // check follow user != self
        $userId = $this->userId();
        if ($followUserId == $userId) {
            throw new BadRequestHttpException('cannot follow self');
        }

        // check if record exists
        if ($this->user()->followings()->wherePivot('follow_user_id', $followUserId)->first()) {
            return new Response('', 200);
        }

        $this->user()->followings()->attach($followUserId);

        return new Response('', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $followUserId
     * @return \Illuminate\Http\Response
     */
    public function unfollow(Request $request)
    {
        $request->validate([
            'follow_user_id' => 'required|int|exists:users,id',
        ]);

        $followUserId = $request->input('follow_user_id');

        // check if record not exists
        if (!$this->user()->followings()->wherePivot('follow_user_id', $followUserId)->first()) {
            return new Response('', 200);
        }

        if ($this->user()->followings()->detach($followUserId)) {
            return new Response('', 200);
        }

        throw new BadRequestHttpException('unfollow failed');
    }
}
