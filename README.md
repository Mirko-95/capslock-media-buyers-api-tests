# CapsLock Media Buyers API Tests

This repository contains a small Codeception API test design for the Media Buyers contract.

The API is not available as a running service, so the focus is on test structure, coverage, and maintainability rather than a green test run against a real environment.

## Test Setup

The intended stack is PHP with Codeception:

- `REST` module for JSON API requests and response assertions.
- `PhpBrowser` as the HTTP transport behind the REST module.
- `Asserts` for normal PHPUnit-style assertions.
- `opis/json-schema` for validating successful responses against JSON Schema.

`BASE_URL` is read from environment configuration and should point to the host without `/api`, for example:

```bash
BASE_URL=https://qa.example.com vendor/bin/codecept run Api
```

## Repository Structure

- `tests/Api/MediaBuyersCest.php` - API scenarios for `GET /mediabuyers` and `POST /mediabuyers`.
- `tests/_support/Api/MediaBuyersApi.php` - small wrapper around the HTTP boundary.
- `tests/_support/Builders/MediaBuyerPayloadBuilder.php` - request payload builder so tests do not hard-code JSON.
- `tests/_support/Schema/JsonSchemaValidator.php` - helper that validates responses against JSON Schema.
- `tests/schemas/` - schemas provided by the assignment contract.
- `tests/_data/valid-media-buyer-payload.json` - reference fixture for a valid create request.
- `tests/Api.suite.yml` - intended Codeception API suite configuration.

## Selected Scenarios

I selected scenarios that protect the most important contract promises:

- The list endpoint always returns `200`, JSON, and a `data` array.
- Media buyer list items contain required fields and valid business values.
- A valid create request returns the created object with a server-generated positive `id`.
- Boolean `active` values are stored as integer `1` or `0` in the response.
- Required fields are enforced.
- Main validation rules are enforced for `email`, `initials`, `name`, `mbId`, and `active`.
- Duplicate `mbId` is rejected.

I intentionally did not add tests for performance, authorization, pagination, sorting, or filtering because the contract does not describe those behaviors.

## Abstractions

`MediaBuyersApi` keeps headers, paths, and response decoding out of the tests. If the endpoint path or authentication changes later, it changes in one place.

`MediaBuyerPayloadBuilder` keeps payload setup readable. Tests describe only the field that matters for the scenario.

`JsonSchemaValidator` makes schema checks reusable and keeps successful response validation consistent.

## Assumptions

- `BASE_URL` is provided by the test environment and already points to the correct host.
- Duplicate `mbId` may return either `400` or `409`; the test accepts both because the assignment leaves this open.
- Optional fields such as `initials` and `slackUserId` may be omitted from the request, but successful responses still include all fields required by the response schema.
- Exact validation message wording may change, so most negative tests assert that the error mentions the important field or invalid value.

## Future Improvements

Once a real environment exists, I would add:

- CI execution against a stable QA environment.
- Test data setup and cleanup so duplicate checks are reliable.
- Contract drift checks using an OpenAPI file, schema versioning, or provider/consumer contract tests.
- Parallelization after data isolation is solved.
- Reporting with screenshots/logs only when failures need extra context.
- Authentication tests would be added once authentication requirements are defined.
