<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ContributorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContributorRepository::class)]
class Contributor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\Column(length: 255)]
        private string $login,

        #[ORM\Column]
        private int $contributions,

        #[ORM\Column(length: 255)]
        private string $profileUrl,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getContributions(): int
    {
        return $this->contributions;
    }

    public function getProfileUrl(): string
    {
        return $this->profileUrl;
    }
}
