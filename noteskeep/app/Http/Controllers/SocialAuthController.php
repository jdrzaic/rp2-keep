<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Socialite;
use App\SocialAccountService;

/**
 * Controller handles authentication using Facebook and Google.
 * Class SocialAuthController
 * @package App\Http\Controllers
 */
class SocialAuthController extends Controller
{
    /**
     * @param $provider
     * @return mixed
     */
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Method called after authentication, redirects to home
     * @param SocialAccountService $service
     * @param $provider
     * @return mixed
     */
    public function callback(SocialAccountService $service, $provider)
    {
        $user = $service->createOrGetUser(Socialite::driver($provider)->user(), $provider);
	auth()->login($user);
        return redirect()->to('/home');
    }
}
