<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Manifay API',
    version: '1.0.0',
)]
#[OA\Server(url: '/api')]
class OpenApiSpec {}
