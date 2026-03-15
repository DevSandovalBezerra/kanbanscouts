<?php

declare(strict_types=1);

return [
    'login' => [
        'max_attempts' => (int) (getenv('LOGIN_RATE_LIMIT_MAX_ATTEMPTS') ?: 5),
        'window_seconds' => (int) (getenv('LOGIN_RATE_LIMIT_WINDOW_SECONDS') ?: 60)
    ]
];
