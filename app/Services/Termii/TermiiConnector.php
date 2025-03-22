<?php

namespace App\Services\Termii;

class TermiiConnector
{
    protected int $connectTimeout = 30;

    protected int $requestTimeout = 60;

    protected TokenResource $tokenResource;

    public function __construct(
        protected readonly string $apiKey,
        protected readonly string $baseUrl
    ) {
    }

    public function resolveBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function tokenApi(): TokenResource
    {
        return $this->tokenResource ??= new TokenResource($this);
    }
}
