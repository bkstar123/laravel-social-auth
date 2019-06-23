<?php
/**
 * SocialAuthenticable trait
 *
 * @author: tuanha
 * @last-mod: 22-06-2019
 */
namespace Bkstar123\SocialAuth\Traits;

use App\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Bkstar123\SocialAuth\Models\SocialAccount;

trait SocialAuthenticable
{
    /**
     * Redirect user to the social provider for authorization
     *
     * @param string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToSocialProvider(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Use the authorization code from social provider to exchange for the access token
     *
     * @param string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleSocialProviderCallback(string $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            return $this->actionIfFailingToGetSocialData();
        }

        $authUser = $this->findOrCreateUserFromSocialAccount($socialUser, $provider);

        if (method_exists($this, 'guard')) {
            $this->guard()->login($authUser, true);
        } else {
            Auth::guard()->login($authUser, true);
        }

        return $this->postSocialLogIn();
    }

    /**
     * Check if the given social account is already associated with a user
     * if not, then create the user & associate it with the social account
     *
     * @param object $socialUser
     * @param string  $provider
     * @return \App\User
     */
    protected function findOrCreateUserFromSocialAccount($socialUser, $provider)
    {
        // check if the user has ever logged in with this social account
        $account = $this->getSocialAccountModelClass()::where('provider_name', $provider)
                            ->where('provider_user_id', $socialUser->getId())
                            ->first();

        if ($account) {
            return $account->user;
        } else {
            $user = $this->getUserModelClass()::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                $user = $this->getUserModelClass()::create($this->mapUserWithSocialData($socialUser));
            }

            $user->accounts()->create([
                'provider_name' => $provider,
                'provider_user_id' => $socialUser->getId()
            ]);

            $this->beforeFirstSocialLogin($user, $socialUser);

            return $user;
        }
    }

    /**
     * Return the class name of the user model
     * This method can be overwritten by an hosting class
     * @return string
     */
    protected function getUserModelClass()
    {
        return User::class;
    }

    /**
     * Return the class name of the social account model
     * This method can be overwritten by an hosting class
     * @return string
     */
    protected function getSocialAccountModelClass()
    {
        return SocialAccount::class;
    }

    /**
     * An after hook which is to be call after a social login
     * This method can be overwritten by an hosting class
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function postSocialLogIn()
    {
        return method_exists($this, 'authenticated') && $this->authenticated(request(), $this->guard()->user())
                ?: redirect()->intended($this->redirectPath());
    }

    /**
     * Map the user with his/her social data
     * This method can be overwritten by an hosting class
     * @return array
     */
    protected function mapUserWithSocialData($socialUser)
    {
        return [
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'avatar' => $socialUser->getAvatar(),
        ];
    }

    /**
     * Specify the action to be taken after the failure of getting user from a social provider
     * This method can be overwritten by an hosting class
     */
    protected function actionIfFailingToGetSocialData()
    {
        return redirect()->route('login');
    }

    /**
     * A before hook which is to be called right before the first social login
     * This method can be overwritten by an hosting class
     * @param $user
     * @param $socialUser
     */
    protected function beforeFirstSocialLogin($user, $socialUser)
    {
        // optional, to be implemented on the hosting class
    }
}
