<?php

declare(strict_types=1);

namespace App\Helpers;

final class HttpResponse
{
    public function __construct(
        private readonly int $statusCode,
        private readonly array $headers = [],
        private readonly string $body = ''
    ) {
    }

    public static function json(array $data, int $statusCode = 200, array $headers = []): self
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            $payload = '{}';
        }

        $headers = array_merge(
            ['content-type' => 'application/json; charset=utf-8'],
            self::normalizeHeaders($headers)
        );

        return new self($statusCode, $headers, $payload);
    }

    public static function text(string $body, int $statusCode = 200, array $headers = []): self
    {
        $headers = array_merge(
            ['content-type' => 'text/plain; charset=utf-8'],
            self::normalizeHeaders($headers)
        );

        return new self($statusCode, $headers, $body);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function header(string $name): ?string
    {
        $needle = strtolower($name);
        foreach ($this->headers as $key => $value) {
            if (strtolower((string) $key) === $needle) {
                return is_array($value) ? implode(',', $value) : (string) $value;
            }
        }

        return null;
    }

    public function body(): string
    {
        return $this->body;
    }

    private static function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $key => $value) {
            $normalized[strtolower((string) $key)] = $value;
        }

        return $normalized;
    }
}
