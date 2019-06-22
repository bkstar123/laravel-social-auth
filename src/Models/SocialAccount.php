<?php
/**
 * SocialAccount model
 *
 * @author: tuanha
 * @last-mod: 22-06-2019
 */
namespace Bkstar123\SocialAuth\Models;

use App\User;
use Bkstar123\SocialAuth\Models\Abstracts\SocialAccountBase;

class SocialAccount extends SocialAccountBase
{
    /**
     * Return the class name of the user model
     * @return string
     */
    protected function getUserModelClass()
    {
        return User::class;
    }
}
