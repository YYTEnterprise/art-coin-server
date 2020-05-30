<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    use ThrottlesLogins;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('api');
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param Request $request
     * @return Response
     */
    public function register(Request $request)
    {
        $data = $request->all();

        Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string'],
        ])->validate();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->wallet()->save(new Wallet());

        event(new Registered($user));

        return new Response('', 201);
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'nickname' => 'string|max:20',
            'avatar_url' => 'string|max:255',
            'cover_image_url' => 'string|max:255',
        ]);

        $userId = $this->userId();
        $user = User::findOrFail($userId);
        $user->update($request->only([
            'nickname',
            'avatar_url',
            'cover_image_url',
        ]));

        return $user;
    }

    public function setPayPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
//            'password_confirmation' => 'required|confirmed|string|min:6',
        ]);

        $user = $this->user();
        $user->update([
            'pay_passwd' => Hash::make($request->input('password')),
        ]);

        return $user;
    }

    public function myInfo()
    {
        return User::withCount('products')
            ->withCount('followers')
            ->with('wallet')
            ->findOrFail($this->userId());
    }

    public function userInfo($id)
    {
        return User::findOrFail($id);
    }

    public function productList(Request $request, $id)
    {
        $request->validate([
            'page' => 'integer',
            'per_page' => 'integer',
        ]);

        $per_page = 10;

        if ($request->has('per_page')) {
            $per_page = $request->input('per_page');
        }

        $user = User::findOrFail($id);

        return $user()
            ->products()
            ->withCount('likes')
            ->with('auction')
            ->paginate($per_page);
    }

    public function followingsList(Request $request, $id)
    {
        $request->validate([
            'page' => 'integer',
            'per_page' => 'integer',
        ]);

        $per_page = 10;

        if ($request->has('per_page')) {
            $per_page = $request->input('per_page');
        }

        $user = User::findOrFail($id);

        return $user->followings()->paginate($per_page);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only('email', 'password');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param Request $request
     * @return mixed
     */
    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        $apiToken = Str::random(60);
        $user = $this->guard()->user();
        $user['api_token'] = $apiToken;
        $user->save();

        $user = User::withCount('products')
            ->withCount('followers')
            ->with('wallet')
            ->findOrFail($user['id']);
        $user['token'] = $apiToken;

        return $user;
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }
}
