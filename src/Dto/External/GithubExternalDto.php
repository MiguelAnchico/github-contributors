<?php

namespace App\Dto\External;

final class GithubExternalDto
{
    public function __construct(
        public readonly string $login,
        public readonly int $contributions,
        public readonly string $profileUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            login: $data['login'],
            contributions: $data['contributions'],
            profileUrl: "https://github.com/{$data['login']}",
        );
    }
}
