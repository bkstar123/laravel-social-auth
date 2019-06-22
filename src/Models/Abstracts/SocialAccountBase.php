<?php
/**
 * Abstract social account class
 *
 * @author: tuanha
 * @last-mod: 22-06-2019
 */
namespace Bkstar123\SocialAuth\Models\Abstracts;

use Illuminate\Database\Eloquent\Model;

abstract class SocialAccountBase extends Model
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
     * A social account belongs to one user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo($this->getUserModelClass());
    }

    abstract protected function getUserModelClass();
}
