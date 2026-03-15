<?php

declare(strict_types=1);

return [
    'idle_timeout_seconds' => (int) (getenv('SESSION_IDLE_TIMEOUT_SECONDS') ?: 1800)
];
