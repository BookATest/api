<?php

namespace App\Models;

use App\Models\Mutators\ServiceUserMutators;
use App\Models\Relationships\ServiceUserRelationships;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ServiceUser extends Model
{
    use ServiceUserMutators;
    use ServiceUserRelationships;

    const PHONE = 'phone';
    const EMAIL = 'email';
    const BOTH = 'both';

    const CACHE_KEY_FOR_ACCESS_CODE = 'ServiceUser::AccessCode::%s';
    const CACHE_KEY_FOR_ACCESS_CODE_ATTEMPTS = 'ServiceUser::AccessCodeAttempts::%s';
    const CACHE_KEY_FOR_TOKEN = 'ServiceUser::Token::%s';

    /**
     * @return string
     */
    public function generateAccessCode(): string
    {
        do {
            $accessCode = mt_rand(10000, 99999);
            $cacheKey = sprintf(static::CACHE_KEY_FOR_ACCESS_CODE, $accessCode);
            $attemptsCacheKey = sprintf(static::CACHE_KEY_FOR_ACCESS_CODE_ATTEMPTS, $accessCode);
        } while (Cache::has($cacheKey));

        Cache::put(
            $cacheKey,
            $this->id,
            config('cache.lifetimes.service_user_access_code') * 60
        );
        Cache::put(
            $attemptsCacheKey,
            0,
            config('cache.lifetimes.service_user_access_code') * 60
        );

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
        $attemptsCacheKey = sprintf(static::CACHE_KEY_FOR_ACCESS_CODE_ATTEMPTS, $accessCode);

        // If the access code doesn't exist, then fail.
        if (!Cache::has($cacheKey)) {
            return false;
        }

        // If the access code has had too many attempts, then fail and remove from cache.
        if (Cache::get($attemptsCacheKey) > config('bat.max_service_user_token_attempts')) {
            Cache::forget($cacheKey);
            Cache::forget($attemptsCacheKey);

            return false;
        }

        // If a phone number was provided, then check that the access code belongs to the user.
        if ($phone) {
            $serviceUser = static::findByPhone($phone);
            $accessCodeMatches = Cache::get($cacheKey) === $serviceUser->id;

            // Keep track of failed attempts.
            if (!$accessCodeMatches) {
                Cache::increment($attemptsCacheKey);

                return false;
            }
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
        $token = Str::random(10);

        Cache::put(
            sprintf(static::CACHE_KEY_FOR_TOKEN, $token),
            $this->id,
            config('cache.lifetimes.service_user_token') * 60
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
