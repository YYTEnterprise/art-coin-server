<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserAddressController extends Controller
{
    public function index()
    {
        return $this->user()->addresses;
    }

    public function show($id)
    {
        return $this->user()->addresses()->findOrFail($id);
    }

    public function store(Request $request)
    {
        $user = $this->user();
        $userAddress = $user->addresses()->create($request->only([
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
        ]));

        return $userAddress;
    }

    public function update(Request $request, $id)
    {
        $user = $this->user();
        $userAddress = $user->addresses()->findOrFail($id);
        $userAddress->update($request->only([
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
        ]));

        return $userAddress;
    }

    public function destroy($id)
    {
        $this->user()->addresses()->delete($id);

        return new Response('', 200);
    }
}
