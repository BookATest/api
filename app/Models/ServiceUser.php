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
    const BOTH = 'both';

    const CACHE_KEY_FOR_ACCESS_CODE = 'ServiceUser::AccessCode::%s';
    const CACHE_KEY_FOR_TOKEN = 'ServiceUser::Token::%s';

    /**
     * @return string
     */
    public function generateAccessCode(): string
    {
        do {
            $accessCode = mt_rand(10000, 99999);
            $cacheKey = sprintf(static::CACHE_KEY_FOR_ACCESS_CODE, $accessCode);
        } while (Cache::has($cacheKey));

        Cache::put($cacheKey, $this->id, config('cache.lifetimes.service_user_access_code'));

        return $accessCode;
    }

    /**
     * @param string $accessCode
     * @param string|null $phone
     * @return bool
     */
    public static function validateAccessCode(string $accessCode, string $phone = null): bool
    {
        $cacheKey = sprintf(static::CACHE_KEY_FOR_ACCESS_CODE, $accessCode);
        $inCache = Cache::has($cacheKey);

        if (!$inCache) {
            return false;
        }

        // If a phone number was provided, then check that the access code belongs to the user.
        if ($phone) {
            return Cache::get($cacheKey) === static::findByPhone($phone)->id;
        }

        // Otherwise, return true, as in cache.
        return true;
    }

    /**
     * @param string $accessCode
     * @return \App\Models\ServiceUser|null
     */
    public static function findByAccessCode(string $accessCode): ?self
    {
        $cacheKey = sprintf(static::CACHE_KEY_FOR_ACCESS_CODE, $accessCode);
        $serviceUserId = Cache::get($cacheKey);

        return $serviceUserId ? static::findOrFail($serviceUserId) : null;
    }

    /**
     * @return string
     */
    public function generateToken(): string
    {
        $token = str_random(10);

        Cache::put(
            sprintf(static::CACHE_KEY_FOR_TOKEN, $token),
            $this->id,
            config('cache.lifetimes.service_user_token')
        );

        return $token;
    }

    /**
     * @param string $token
     * @return bool
     */
    public static function validateToken(string $token): bool
    {
        $cacheKey = sprintf(static::CACHE_KEY_FOR_TOKEN, $token);

        return Cache::has($cacheKey);
    }

    /**
     * @param string $token
     * @return \App\Models\ServiceUser|null
     */
    public static function findByToken(string $token): ?self
    {
        $cacheKey = sprintf(static::CACHE_KEY_FOR_TOKEN, $token);
        $serviceUserId = Cache::get($cacheKey);

        return $serviceUserId ? static::findOrFail($serviceUserId) : null;
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
