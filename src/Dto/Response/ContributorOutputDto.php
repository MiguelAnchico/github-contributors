<?php

declare(strict_types=1);

namespace App\Dto\Response;

use App\Dto\External\GithubExternalDto;
use App\Entity\Contributor;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class ContributorOutputDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $login,

        #[Assert\PositiveOrZero]
        public int $contributions,

        #[Assert\NotBlank]
        #[Assert\Url]
        #[SerializedName('profile_url')]
        public string $profileUrl,
    ) {}

    public static function fromEntity(Contributor $contributor): self
    {
        return new self(
            $contributor->getLogin(),
            $contributor->getContributions(),
            $contributor->getProfileUrl(),
        );
    }

    public static function fromExternal(GithubExternalDto $dto): self
    {
        return new self(
            $dto->login,
            $dto->contributions,
            $dto->profileUrl,
        );
    }
}
