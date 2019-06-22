<?php
/**
 * SocialAccount model
 *
 * @author: tuanha
 * @last-mod: 22-06-2019
 */
namespace Bkstar123\SocialAuth\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider_name', 'provider_id'
    ];

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
     * A social account belongs to one user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo($this->getUserModelClass());
    }
}
