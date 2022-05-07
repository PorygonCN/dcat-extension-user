<?php

namespace Porygon\User\Traits;

use Porygon\User\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToUser
{
    public static function bootBelongsToUser()
    {
        static::addGlobalScope("onlyComplete", function (Builder $query) {
            $query->has("user");
        });
        static::addGlobalScope("enable", function (Builder $query) {
            $query->whereHas("user", function ($query) {
                $query->where("enable", true);
            });
        });
        static::addGlobalScope("belongsToUser", function (Builder $query) {
            $query->with("user");
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
