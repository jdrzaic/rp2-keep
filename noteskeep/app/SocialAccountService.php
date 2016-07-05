<?php
/**
 * Created by PhpStorm.
 * User: jelenadrzaic
 * Date: 27/06/16
 * Time: 21:03
 */

namespace App;

use Laravel\Socialite\Contracts\User as ProviderUser;

/**
 * Class handles authorization of a user, through third party services
 * Class SocialAccountService
 * @package App
 */
class SocialAccountService
{
    /**
     * Method returns user associated with user authenticated through third party, or creates a new user.
     * @param ProviderUser $providerUser user authenticated through third party
     * @param $provider facebook/google
     * @return authenticated user
     */
    public function createOrGetUser(ProviderUser $providerUser, $provider)
    {
        $account = SocialAccount::whereProvider($provider)
            ->whereProviderUserId($providerUser->getId())
            ->first();

        if ($account) {
            return $account->user;
        } else {

            $account = new SocialAccount([
                'provider_user_id' => $providerUser->getId(),
                'provider' => $provider
            ]);

            $user = User::whereEmail($providerUser->getEmail())->first();

            if (!$user) {

                $user = User::create([
                    'email' => $providerUser->getEmail(),
                    'name' => $providerUser->getName(),
                ]);
            }

            $account->user()->associate($user);
            $account->save();

            return $user;

        }

    }
}