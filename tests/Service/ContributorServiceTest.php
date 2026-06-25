<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\ApiClient\GithubApiClient;
use App\Dto\External\GithubExternalDto;
use App\Entity\Contributor;
use App\Repository\ContributorRepository;
use App\Service\ContributorService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class ContributorServiceTest extends TestCase
{
    public function testStoreTopContributorsKeepsTheFiveMostActiveOrdered(): void
    {
        $contributors = [
            new GithubExternalDto('user1', 10, 'https://github.com/user1'),
            new GithubExternalDto('user2', 90, 'https://github.com/user2'),
            new GithubExternalDto('user3', 50, 'https://github.com/user3'),
            new GithubExternalDto('user4', 70, 'https://github.com/user4'),
            new GithubExternalDto('user5', 30, 'https://github.com/user5'),
            new GithubExternalDto('user6', 100, 'https://github.com/user6'),
            new GithubExternalDto('user7', 20, 'https://github.com/user7'),
        ];

        $apiClient = $this->createMock(GithubApiClient::class);
        $apiClient->method('getContributors')->willReturn($contributors);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(ContributorRepository::class);

        $service = new ContributorService($repository, $apiClient, $entityManager);

        $top = $service->storeTopContributors('owner', 'repo');

        self::assertCount(5, $top);
        self::assertSame(
            [100, 90, 70, 50, 30],
            array_map(fn (GithubExternalDto $dto) => $dto->contributions, $top),
        );
        self::assertSame('user6', $top[0]->login);
    }

    public function testStoreTopContributorsPersistsEachOneAndFlushesOnce(): void
    {
        $contributors = [
            new GithubExternalDto('user1', 10, 'https://github.com/user1'),
            new GithubExternalDto('user2', 20, 'https://github.com/user2'),
        ];

        $apiClient = $this->createMock(GithubApiClient::class);
        $apiClient->method('getContributors')->willReturn($contributors);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::exactly(2))
            ->method('persist')
            ->with(self::isInstanceOf(Contributor::class));
        $entityManager->expects(self::once())->method('flush');

        $repository = $this->createMock(ContributorRepository::class);

        $service = new ContributorService($repository, $apiClient, $entityManager);

        $service->storeTopContributors('owner', 'repo');
    }

    public function testGetStoredContributorsDelegatesToRepository(): void
    {
        $expected = [new Contributor('user1', 50, 'https://github.com/user1')];

        $repository = $this->createMock(ContributorRepository::class);
        $repository->expects(self::once())
            ->method('findPaginated')
            ->with(2, 10, 'user', 5)
            ->willReturn($expected);

        $apiClient = $this->createMock(GithubApiClient::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $service = new ContributorService($repository, $apiClient, $entityManager);

        $result = $service->getStoredContributors(2, 10, 'user', 5);

        self::assertSame($expected, $result);
    }
}
