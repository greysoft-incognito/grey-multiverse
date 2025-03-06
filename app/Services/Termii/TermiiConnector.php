<?php

namespace App\Services\Termii;

use Okolaa\TermiiPHP\Resources\Campaign\CampaignResource;
use Okolaa\TermiiPHP\Resources\InsightResource;
use Okolaa\TermiiPHP\Resources\MessagingResource;
use Okolaa\TermiiPHP\Resources\SenderIdResource;

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
