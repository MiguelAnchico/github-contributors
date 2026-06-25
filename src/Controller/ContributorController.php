<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Response\ContributorOutputDto;
use App\Service\ContributorService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Contributors')]
#[Route(path: '/api/contributors', name: 'contributors')]
final class ContributorController extends AbstractController
{
    public function __construct(
        private readonly ContributorService $contributorService,
    ) {}

    #[Route('/{owner}/{repository}/top', methods: ['POST'])]
    #[OA\Response(
        response: 200,
        description: 'Fetches contributors from GitHub, stores the top 5 and returns them',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ContributorOutputDto::class)),
        ),
    )]
    public function storeTop(string $owner, string $repository): JsonResponse
    {
        $top = $this->contributorService->storeTopContributors($owner, $repository);

        return $this->json(array_map(
            ContributorOutputDto::fromExternal(...),
            $top,
        ));
    }

    #[Route('', methods: ['GET'])]
    #[OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 5))]
    #[OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string'))]
    #[OA\Parameter(name: 'minContributions', in: 'query', schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(
        response: 200,
        description: 'Returns the stored contributors, paginated and filtered',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ContributorOutputDto::class)),
        ),
    )]
    public function list(
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $limit = 5,
        #[MapQueryParameter] ?string $search = null,
        #[MapQueryParameter] ?int $minContributions = null,
    ): JsonResponse {
        $contributors = $this->contributorService->getStoredContributors(
            $page,
            $limit,
            $search,
            $minContributions,
        );

        return $this->json(array_map(
            ContributorOutputDto::fromEntity(...),
            $contributors,
        ));
    }
}
