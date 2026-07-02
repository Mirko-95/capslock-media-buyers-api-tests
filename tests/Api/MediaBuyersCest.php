<?php

declare(strict_types=1);

namespace Tests\Api;

use Codeception\Example;
use Tests\Support\Api\MediaBuyersApi;
use Tests\Support\ApiTester;
use Tests\Support\Builders\MediaBuyerPayloadBuilder;
use Tests\Support\Schema\JsonSchemaValidator;

final class MediaBuyersCest
{
    private MediaBuyersApi $api;

    public function _before(): void
    {
        $this->api = new MediaBuyersApi();
    }

    public function getMediaBuyersReturnsJsonList(ApiTester $I): void
    {
        $this->api->getAll($I);

        $I->seeResponseCodeIs(200);
        $I->assertStringContainsString('application/json', $I->grabHttpHeader('Content-Type'));
        JsonSchemaValidator::assertResponseMatches($I, codecept_root_dir('tests/schemas/get-media-buyers-schema.json'));

        $response = $this->api->response($I);
        $I->assertArrayHasKey('data', $response);
        $I->assertIsArray($response['data'], 'data should always be an array, including empty state.');
    }

    public function getMediaBuyersItemsHaveValidBusinessFields(ApiTester $I): void
    {
        $this->api->getAll($I);
        $I->seeResponseCodeIs(200);

        $response = $this->api->response($I);
        $ids = [];

        foreach ($response['data'] as $buyer) {
            foreach (['id', 'mbId', 'initials', 'name', 'email', 'slackUserId', 'active'] as $field) {
                $I->assertArrayHasKey($field, $buyer);
            }

            $I->assertMatchesRegularExpression('/^[^@\s]+@[^@\s]+\.[^@\s]+$/', $buyer['email']);
            $I->assertTrue(in_array($buyer['active'], [0, 1], true), 'active must be integer 0 or 1.');
            $I->assertNotContains($buyer['id'], $ids, 'id values must be unique in the list.');
            $ids[] = $buyer['id'];
        }
    }

    public function createMediaBuyerWithValidPayload(ApiTester $I): void
    {
        $payload = MediaBuyerPayloadBuilder::valid()
            ->withMbId('9101')
            ->build();

        $this->api->create($I, $payload);

        $I->seeResponseCodeIs(200);
        $I->assertStringContainsString('application/json', $I->grabHttpHeader('Content-Type'));
        JsonSchemaValidator::assertResponseMatches($I, codecept_root_dir('tests/schemas/post-media-buyer-schema.json'));

        $response = $this->api->response($I);
        $I->assertIsInt($response['data']['id']);
        $I->assertGreaterThan(0, $response['data']['id']);

        foreach (['mbId', 'initials', 'name', 'email', 'slackUserId'] as $field) {
            $I->assertSame($payload[$field], $response['data'][$field]);
        }
    }

    /**
     * @dataProvider activeExamples
     */
    public function createMediaBuyerMapsBooleanActiveToInteger(ApiTester $I, Example $example): void
    {
        $payload = MediaBuyerPayloadBuilder::valid()
            ->withMbId($example['mbId'])
            ->withActive($example['requestActive'])
            ->build();

        $this->api->create($I, $payload);

        $I->seeResponseCodeIs(200);
        $response = $this->api->response($I);
        $I->assertSame($example['responseActive'], $response['data']['active']);
    }

    public function createMediaBuyerRejectsMissingRequiredFields(ApiTester $I): void
    {
        $payload = MediaBuyerPayloadBuilder::valid()
            ->without('mbId')
            ->without('name')
            ->without('email')
            ->without('active')
            ->build();

        $this->api->create($I, $payload);

        $I->seeResponseCodeIs(400);
        $response = $this->api->response($I);
        $I->assertArrayHasKey('errors', $response);

        foreach (['mbId', 'name', 'email', 'active'] as $field) {
            $this->assertErrorMentions($I, $response, $field);
        }
    }

    public function createMediaBuyerRejectsInvalidEmail(ApiTester $I): void
    {
        $payload = MediaBuyerPayloadBuilder::valid()
            ->withMbId('9102')
            ->withEmail('not-an-email')
            ->build();

        $this->api->create($I, $payload);

        $I->seeResponseCodeIs(400);
        $this->assertErrorMentions($I, $this->api->response($I), 'not-an-email');
    }

    public function createMediaBuyerRejectsInitialsLongerThanTwoCharacters(ApiTester $I): void
    {
        $payload = MediaBuyerPayloadBuilder::valid()
            ->withMbId('9103')
            ->withInitials('TOO LONG')
            ->build();

        $this->api->create($I, $payload);

        $I->seeResponseCodeIs(400);
        $this->assertErrorMentions($I, $this->api->response($I), 'exactly 2 characters');
    }

    /**
     * @dataProvider invalidNameExamples
     */
    public function createMediaBuyerRejectsInvalidNameLength(ApiTester $I, Example $example): void
    {
        $payload = MediaBuyerPayloadBuilder::valid()
            ->withMbId($example['mbId'])
            ->withName($example['name'])
            ->build();

        $this->api->create($I, $payload);

        $I->seeResponseCodeIs(400);
        $this->assertErrorMentionsAny($I, $this->api->response($I), ['length', 'characters', 'name']);
    }

    public function createMediaBuyerRejectsInvalidMbId(ApiTester $I): void
    {
        $payload = MediaBuyerPayloadBuilder::valid()
            ->withMbId('abc')
            ->build();

        $this->api->create($I, $payload);

        $I->seeResponseCodeIs(400);
        $this->assertErrorMentions($I, $this->api->response($I), 'mbId');
    }

    public function createMediaBuyerRejectsNonBooleanActive(ApiTester $I): void
    {
        $payload = MediaBuyerPayloadBuilder::valid()
            ->withMbId('9104')
            ->withActive('yes')
            ->build();

        $this->api->create($I, $payload);

        $I->seeResponseCodeIs(400);
        $this->assertErrorMentions($I, $this->api->response($I), 'active');
    }

    public function createMediaBuyerRejectsDuplicateMbId(ApiTester $I): void
    {
        $payload = MediaBuyerPayloadBuilder::valid()
            ->withMbId('9105')
            ->build();

        $this->api->create($I, $payload);
        $I->seeResponseCodeIs(200);

        $this->api->create($I, $payload);

        $I->assertContains($I->grabResponseCode(), [400, 409]);
        $this->assertErrorMentionsAny($I, $this->api->response($I), ['mbId', 'unique', 'duplicate']);
    }

    protected function activeExamples(): array
    {
        return [
            ['mbId' => '9201', 'requestActive' => true, 'responseActive' => 1],
            ['mbId' => '9202', 'requestActive' => false, 'responseActive' => 0],
        ];
    }

    protected function invalidNameExamples(): array
    {
        return [
            ['mbId' => '9301', 'name' => 'A'],
            ['mbId' => '9302', 'name' => 'This name is longer than thirty chars'],
        ];
    }

    private function assertErrorMentions(ApiTester $I, array $response, string $expectedText): void
    {
        $this->assertErrorMentionsAny($I, $response, [$expectedText]);
    }

    private function assertErrorMentionsAny(ApiTester $I, array $response, array $expectedTexts): void
    {
        $I->assertArrayHasKey('errors', $response);
        $details = array_column($response['errors'], 'detail');

        $I->assertNotEmpty(
            array_filter($details, function (string $detail) use ($expectedTexts): bool {
                foreach ($expectedTexts as $expectedText) {
                    if (str_contains($detail, $expectedText)) {
                        return true;
                    }
                }

                return false;
            }),
            sprintf('Expected validation error to mention one of: %s.', implode(', ', $expectedTexts))
        );
    }
}
