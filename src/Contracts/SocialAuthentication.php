<?php
/**
 * SocialAuthentication contract
 *
 * @author: tuanha
 * @last-mod: 22-06-2019
 */
namespace Bkstar123\SocialAuth\Contracts;

interface SocialAuthentication
{
    /**
     * Redirect user to the social provider for authorization
     *
     * @param string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToProvider(string $provider);

    /**
     * Use the authorization code from social provider to exchange for the access token
     *
     * @param string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback(string $provider);
}
