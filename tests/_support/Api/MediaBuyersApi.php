<?php

declare(strict_types=1);

namespace Tests\Support\Api;

use Tests\Support\ApiTester;

final class MediaBuyersApi
{
    private const RESOURCE = '/mediabuyers';

    public function getAll(ApiTester $I): void
    {
        $this->setJsonHeaders($I);
        $I->sendGet(self::RESOURCE);
    }

    public function create(ApiTester $I, array $payload): void
    {
        $this->setJsonHeaders($I);
        $I->sendPost(self::RESOURCE, $payload);
    }

    public function response(ApiTester $I): array
    {
        $decoded = json_decode($I->grabResponse(), true);

        $I->assertIsArray($decoded, 'Response should be valid JSON object or array.');

        return $decoded;
    }

    private function setJsonHeaders(ApiTester $I): void
    {
        $I->haveHttpHeader('Accept', 'application/json');
        $I->haveHttpHeader('Content-Type', 'application/json');
    }
}
