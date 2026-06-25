<?php

declare(strict_types=1);

namespace App\ApiClient;

use App\Dto\External\GithubExternalDto;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Thin wrapper around the GitHub REST API.
 * Only knows how to talk to GitHub and map raw responses into DTOs.
 */
final class GithubApiClient
{
    public function __construct(
        private readonly HttpClientInterface $apiClient,
        private readonly string $baseUrl,
        private readonly string $apiToken
    ) {}

    public function getContributors(string $owner, string $repository): array
    {
        $response = $this->apiClient->request(
            method: 'GET',
            url: "$this->baseUrl/repos/$owner/$repository/contributors",
            options: [
                'headers' => [
                    'Authorization' => "Bearer $this->apiToken",
                ]
            ]
        );

        return array_map(
            GithubExternalDto::fromArray(...),
            $response->toArray(),
        );
    }
}
