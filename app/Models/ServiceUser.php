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
     * @return string
     */
    public function generateAccessCode(): string
    {
        $accessCode = mt_rand(10000, 99999);

        Cache::put(
            sprintf(static::CACHE_KEY_FOR_ACCESS_CODE, $this->id),
            $accessCode,
            config('cache.lifetimes.service_user_access_code')
        );

        return $accessCode;
    }

    /**
     * @param string $accessCode
     * @return bool
     */
    public function validateAccessCode(string $accessCode): bool
    {
        $cacheKey = sprintf(static::CACHE_KEY_FOR_ACCESS_CODE, $this->id);

        return Cache::get($cacheKey) === $accessCode;
    }

    /**
     * @return string
     */
    public function generateToken(): string
    {
        $token = str_random(10);

        Cache::put(
            sprintf(static::CACHE_KEY_FOR_TOKEN, $this->id),
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
        $cacheKey = sprintf(static::CACHE_KEY_FOR_TOKEN, $this->id);

        return Cache::get($cacheKey) === $token;
    }

    /**
     * @param string $phone
     * @return \App\Models\ServiceUser|null
     */
    public static function findByPhone(string $phone): ?self
    {
        return static::where('phone', $phone)->first();
    }
}
