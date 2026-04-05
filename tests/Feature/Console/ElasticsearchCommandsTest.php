<?php

use App\Services\ElasticsearchClient;

it('shows the configured alias names in the health command output', function () {
    $client = Mockery::mock(ElasticsearchClient::class);
    $client->shouldReceive('health')->once()->andReturn([
        'cluster_name' => 'larasearch',
        'status' => 'green',
    ]);

    app()->instance(ElasticsearchClient::class, $client);

    $this->artisan('es:health')
        ->expectsOutputToContain('Elasticsearch is reachable.')
        ->expectsOutputToContain('job_listings_current')
        ->assertExitCode(0);
});

it('creates and deletes the configured index', function () {
    $client = Mockery::mock(ElasticsearchClient::class);
    $client->shouldReceive('createIndex')->once()->with('job_listings_v1', Mockery::type('array'))->andReturn(['acknowledged' => true]);
    $client->shouldReceive('deleteIndex')->once()->with('job_listings_v1')->andReturn(['acknowledged' => true]);

    app()->instance(ElasticsearchClient::class, $client);

    $this->artisan('es:create-index')->assertExitCode(0);
    $this->artisan('es:delete-index')->assertExitCode(0);
});

it('switches the alias to the requested index', function () {
    $client = Mockery::mock(ElasticsearchClient::class);
    $client->shouldReceive('updateAliases')->once()->with(Mockery::on(function (array $actions): bool {
        return data_get($actions, '1.add.index') === 'job_listings_v2'
            && data_get($actions, '1.add.alias') === 'job_listings_current';
    }))->andReturn(['acknowledged' => true]);

    app()->instance(ElasticsearchClient::class, $client);

    $this->artisan('es:switch-alias job_listings_v2')
        ->expectsOutputToContain('job_listings_current')
        ->assertExitCode(0);
});
