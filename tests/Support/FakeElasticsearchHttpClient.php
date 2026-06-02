<?php

namespace Tests\Support;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FakeElasticsearchHttpClient implements ClientInterface
{
    /** @var array<int, RequestInterface> */
    public array $requests = [];

    /**
     * @param  array<int, array<string, mixed>>  $responses
     */
    public function __construct(
        private array $responses,
    ) {}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        $body = array_shift($this->responses) ?? [];

        return new Response(
            status: 200,
            headers: [
                'Content-Type' => 'application/json',
                'X-Elastic-Product' => 'Elasticsearch',
            ],
            body: json_encode($body, JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonBody(int $index = 0): array
    {
        $body = (string) $this->requests[$index]->getBody();

        return $body === ''
            ? []
            : json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    }
}
