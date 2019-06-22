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
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Bkstar123\SocialAuth\Exceptions\MethodDoesNotExistException;
use Bkstar123\SocialAuth\Exceptions\PropertyDoesNotExistException;

trait SocialAuthenticable
{
    /**
     * Return the class name of the user model
     * This method can be overwritten by an extending class
     * @return string
     */
    protected function getUserModelClass()
    {
        return User::class;
    }

    /**
     * Redirect user to the social provider for authorization
     *
     * @param string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToProvider(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Use the authorization code from social provider to exchange for the access token
     *
     * @param string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback(string $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            return redirect()->route('login');
        }

        $authUser = $this->findOrCreateUser($socialUser, $provider);

        Auth::login($authUser, true);

        return $this->postLogInAction();
    }

    /**
     * Check if the given social account is already associated with a user
     * if not, then create the user & associate it with the social account
     *
     * @param Socialite user  $socialUser
     * @param string  $provider
     * @return \App\User
     */
    protected function findOrCreateUser($socialUser, $provider)
    {
        if (!property_exists($this, 'socialAccountModel')) {
            throw new PropertyDoesNotExistException('The property socialAccountModel does not exist in '.get_class($this));
        }

        // check if the user has ever logged in with this social account
        $account = $this->socialAccountModel::where('provider_name', $provider)
                                            ->where('provider_id', $socialUser->getId())
                                            ->first();

        if ($account) {
            if (!method_exists($account, 'user')) {
                throw new MethodDoesNotExistException(
                    'The method user() does not exist in '.get_class($account).
                    '. This method must be defined and return the '.BelongsTo::class.' relationship to the associated user'
                );
            }
            return $account->user;
        } else {
            $user = $this->getUserModelClass()::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                $user = $this->getUserModelClass()::create([
                    'email' => $socialUser->getEmail(),
                    'name' => $socialUser->getName(),
                    'social_avatar' => $socialUser->getAvatar(),
                    'username' => $socialUser->getNickName()
                ]);
            }

            if (!method_exists($user, 'accounts')) {
                throw new MethodDoesNotExistException(
                    'The method accounts() does not exist in '.get_class($user).
                    '. This method must be defined and return the '.HasMany::class.' relationship to the associated social accounts'
                );
            }

            $user->accounts()->create([
                'provider_name' => $provider,
                'provider_id' => $socialUser->getid()
            ]);
            
            $user->email_verified_at = $user->email_verified_at ?? Carbon::now();
            
            $user->save();

            return $user;
        }
    }

    /**
     * Take post-login actions
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function postLogInAction()
    {
        return $this->authenticated(request(), $this->guard()->user())
                ?: redirect()->intended($this->redirectPath());
    }
}
