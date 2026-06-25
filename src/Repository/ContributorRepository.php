<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contributor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ContributorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contributor::class);
    }

    public function findPaginated(
        int $page,
        int $limit,
        ?string $search = null,
        ?int $minContributions = null,
    ): array {
        $queryBuilder = $this->createQueryBuilder('c')
            ->orderBy('c.contributions', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if ($search !== null) {
            $queryBuilder
                ->andWhere('c.login LIKE :search')
                ->setParameter('search', "%$search%");
        }

        if ($minContributions !== null) {
            $queryBuilder
                ->andWhere('c.contributions >= :minContributions')
                ->setParameter('minContributions', $minContributions);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
