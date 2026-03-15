<?php

declare(strict_types=1);

namespace App\Helpers;

final class HttpRequest
{
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $headers = [],
        private readonly ?string $body = null,
        private readonly array $query = [],
        private readonly array $post = []
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            $path = '/';
        }

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (!is_string($key)) {
                continue;
            }
            if (str_starts_with($key, 'HTTP_')) {
                $name = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$name] = $value;
            }
        }

        $contentType = $_SERVER['CONTENT_TYPE'] ?? null;
        if (is_string($contentType) && $contentType !== '') {
            $headers['content-type'] = $contentType;
        }

        $body = file_get_contents('php://input');
        if ($body === false) {
            $body = null;
        }

        return new self(
            (string) $method,
            $path,
            $headers,
            $body,
            is_array($_GET ?? null) ? $_GET : [],
            is_array($_POST ?? null) ? $_POST : []
        );
    }

    public function method(): string
    {
        return strtoupper($this->method);
    }

    public function path(): string
    {
        if ($this->path === '') {
            return '/';
        }

        return $this->path[0] === '/' ? $this->path : '/' . $this->path;
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

    public function body(): ?string
    {
        return $this->body;
    }

    public function query(): array
    {
        return $this->query;
    }

    public function post(): array
    {
        return $this->post;
    }
}
