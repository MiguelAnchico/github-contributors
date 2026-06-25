<?php

declare(strict_types=1);

namespace App\Service;

use App\ApiClient\GithubApiClient;
use App\Dto\External\GithubExternalDto;
use App\Entity\Contributor;
use App\Repository\ContributorRepository;
use Doctrine\ORM\EntityManagerInterface;

final class ContributorService
{
    private const TOP_LIMIT = 5;

    public function __construct(
        private readonly ContributorRepository $contributorRepository,
        private readonly GithubApiClient $githubApiClient,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function storeTopContributors(string $owner, string $repository): array
    {
        $contributors = $this->githubApiClient->getContributors($owner, $repository);

        usort($contributors,
            fn (GithubExternalDto $a, GithubExternalDto $b) => $b->contributions <=> $a->contributions,
        );

        $top = array_slice($contributors, 0, self::TOP_LIMIT);

        foreach ($top as $dto) {
            $this->entityManager->persist(new Contributor(
                $dto->login,
                $dto->contributions,
                $dto->profileUrl,
            ));
        }

        $this->entityManager->flush();

        return $top;
    }

    public function getStoredContributors(
        int $page,
        int $limit,
        ?string $search = null,
        ?int $minContributions = null,
    ): array {
        return $this->contributorRepository->findPaginated($page, $limit, $search, $minContributions);
    }
}
