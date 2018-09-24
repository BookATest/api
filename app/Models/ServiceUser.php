<?php

namespace App\Models;

use App\Models\Mutators\ServiceUserMutators;
use App\Models\Relationships\ServiceUserRelationships;
use Illuminate\Support\Facades\Cache;

class ServiceUser extends Model
{
    use ServiceUserMutators;
    use ServiceUserRelationships;

    const PHONE = 'phone';
    const EMAIL = 'email';

    const CACHE_KEY_FOR_ACCESS_CODE = 'ServiceUser::AccessCode::%s';
    const CACHE_KEY_FOR_TOKEN = 'ServiceUser::Token::%s';

    /**
     * @var string The primary key of the table.
     */
    protected $primaryKey = 'uuid';

    /**
     * @return string
     */
    public function generateToken(): string
    {
        $token = str_random(10);

        Cache::put(
            sprintf(static::CACHE_KEY_FOR_TOKEN, $this->uuid),
            $token,
            config('cache.lifetimes.service_user_token')
        );

        return $token;
    }

    /**
     * @param string $token
     * @return bool
     */
    public function validateToken(string $token): bool
    {
        return Cache::get(sprintf(static::CACHE_KEY_FOR_TOKEN, $this->uuid)) === $token;
    }
}
