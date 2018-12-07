<?php

return [

    'otp_enabled' => env('OTP_ENABLED', true),

    'max_service_user_token_attempts' => env('MAX_SERVICE_USER_TOKEN_ATTEMPTS', 3),

    'pagination_results' => env('PAGINATION_RESULTS', 15),

    'max_pagination_results' => env('MAX_PAGINATION_RESULTS', 100),

    'days_in_advance_to_book' => env('DAYS_IN_ADVANCE_TO_BOOK', 30),

];
