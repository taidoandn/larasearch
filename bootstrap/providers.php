<?php

use App\Providers\AppServiceProvider;
use App\Providers\ElasticsearchServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    ElasticsearchServiceProvider::class,
    FortifyServiceProvider::class,
];
