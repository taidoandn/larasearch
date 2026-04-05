<?php

use Elastic\Elasticsearch\Client;

test('the official elasticsearch php client is bound in the container', function () {
    expect(app(Client::class))->toBeInstanceOf(Client::class);
});
