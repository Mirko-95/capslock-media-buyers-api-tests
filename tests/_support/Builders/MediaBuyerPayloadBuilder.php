<?php

declare(strict_types=1);

namespace Tests\Support\Builders;

final class MediaBuyerPayloadBuilder
{
    private array $payload = [
        'mbId' => '9001',
        'initials' => 'TM',
        'name' => 'Test Media Buyer',
        'email' => 'test.media.buyer@example.com',
        'slackUserId' => 'U05AZ3DQBBKK',
        'active' => true,
    ];

    public static function valid(): self
    {
        return new self();
    }

    public function withMbId(string $mbId): self
    {
        $this->payload['mbId'] = $mbId;

        return $this;
    }

    public function withInitials(string $initials): self
    {
        $this->payload['initials'] = $initials;

        return $this;
    }

    public function withoutInitials(): self
    {
        unset($this->payload['initials']);

        return $this;
    }

    public function withName(string $name): self
    {
        $this->payload['name'] = $name;

        return $this;
    }

    public function withEmail(string $email): self
    {
        $this->payload['email'] = $email;

        return $this;
    }

    public function withSlackUserId(string $slackUserId): self
    {
        $this->payload['slackUserId'] = $slackUserId;

        return $this;
    }

    public function withoutSlackUserId(): self
    {
        unset($this->payload['slackUserId']);

        return $this;
    }

    public function withActive(mixed $active): self
    {
        $this->payload['active'] = $active;

        return $this;
    }

    public function without(string $field): self
    {
        unset($this->payload[$field]);

        return $this;
    }

    public function build(): array
    {
        return $this->payload;
    }
}
