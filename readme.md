# GitHub Contributors API

A small Symfony API that fetches a repository's contributors from the GitHub REST
API, ranks the top 5 by number of contributions, stores them, and exposes a
paginated, filterable listing. Built as a layered application (Controller →
Service → Repository / API Client) with OpenAPI documentation.

## Stack

- PHP 8.2+
- Symfony 7
- Doctrine ORM (PostgreSQL)
- Symfony HttpClient
- NelmioApiDocBundle (OpenAPI / Swagger UI)
- PHPUnit

## Requirements

- PHP 8.2 or higher
- Composer
- PostgreSQL
- A GitHub Personal Access Token

## Setup

Clone the repository and install dependencies:

```bash
git clone https://github.com/MiguelAnchico/github-app.git
cd github-app
composer install
```

Create a `.env.local` file with your own values (this file is git-ignored):

```
GITHUB_API_URL=https://api.github.com
GITHUB_API_TOKEN=your_github_token
DATABASE_URL="postgresql://app:app@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
```

Create the database and run the migrations:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

Start the development server:

```bash
symfony serve
```

## Endpoints

### Store top contributors

Fetches contributors from GitHub, ranks the top 5 and persists them.

```
POST /api/contributors/{owner}/{repository}/top
```

Example:

```bash
curl -X POST http://127.0.0.1:8000/api/contributors/symfony/symfony/top
```

### List stored contributors

Returns the stored contributors, ordered by contributions, with pagination,
search and filtering.

```
GET /api/contributors
```

Query parameters:

| Parameter          | Type    | Default | Description                          |
|--------------------|---------|---------|--------------------------------------|
| `page`             | integer | 1       | Page number                          |
| `limit`            | integer | 5       | Items per page                       |
| `search`           | string  | null    | Filters by login (partial match)     |
| `minContributions` | integer | null    | Only contributors with at least this |

Example:

```bash
curl "http://127.0.0.1:8000/api/contributors?page=1&limit=5&search=nicolas-grekas&minContributions=50"
```

## API documentation

Interactive Swagger UI is available at:

```
/api/doc
```

The raw OpenAPI specification is served at `/api/doc.json`.

## Tests

```bash
php bin/phpunit
```

The suite covers the service logic (ranking, top-5 selection and persistence)
and the external DTO mapping.

## Architecture

The application is organized into clear layers, each with a single responsibility:

- **Controller** (`ContributorController`): HTTP layer only. Reads request input,
  delegates to the service and shapes the JSON response. Documented with OpenAPI
  attributes.
- **Service** (`ContributorService`): application logic. Orchestrates fetching,
  ranking, persistence and reads.
- **API Client** (`GithubApiClient`): the only class that talks to GitHub. Maps
  raw responses into DTOs.
- **Repository** (`ContributorRepository`): read access to the database, including
  the paginated and filtered query.
- **DTOs**: `GithubExternalDto` represents the data coming from GitHub;
  `ContributorOutputDto` represents the serialized API response.
- **Entity** (`Contributor`): the persisted model.

## Technical decisions

**Ranking is done explicitly in PHP.** The GitHub API already returns contributors
ordered by contributions, but the service sorts them again with `usort` and the
spaceship operator before slicing the top 5. The ordering is part of the exercise
and does not rely on the upstream order being guaranteed.

**Writes go through the EntityManager, reads through the Repository.** The service
persists entities using `EntityManagerInterface`, while the repository stays
focused on queries. This keeps the persistence transaction owned by the service
and the repository limited to read concerns.

**The profile URL is derived, not trusted.** Instead of reusing GitHub's
`html_url`, the contributor profile URL is built from the login, keeping the
stored data under the application's control.

**Output shape is decoupled from the entity.** `ContributorOutputDto` defines the
exact public response (including the `profile_url` snake_case field via
`SerializedName`), so the internal entity can evolve without changing the API
contract.
