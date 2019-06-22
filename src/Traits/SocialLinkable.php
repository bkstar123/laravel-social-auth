<?php
/**
 * SocialLinkable trait
 *
 * @author: tuanha
 * @last-mod: 22-06-2019
 */
namespace Bkstar123\SocialAuth\Traits;

use Bkstar123\SocialAuth\Models\SocialAccount;

trait SocialLinkable
{
    /**
     * a user can have many associated social accounts
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accounts()
    {
        return $this->hasMany($this->getSocialAccountModelClass());
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
}
