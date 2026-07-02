<?php

declare(strict_types=1);

namespace Tests\Support\Schema;

use Opis\JsonSchema\Validator;
use Tests\Support\ApiTester;

final class JsonSchemaValidator
{
    public static function assertResponseMatches(ApiTester $I, string $schemaPath): void
    {
        $response = json_decode($I->grabResponse());
        $schema = json_decode(file_get_contents($schemaPath));

        $I->assertNotNull($response, 'Response must be valid JSON.');
        $I->assertNotNull($schema, 'Schema file must be valid JSON.');

        $result = (new Validator())->validate($response, $schema);

        $I->assertTrue(
            $result->isValid(),
            sprintf('Response does not match JSON schema: %s', $schemaPath)
        );
    }
}
