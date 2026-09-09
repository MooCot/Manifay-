<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    // фронтенд у dev — localhost:3000 (docker-compose), CORS замість Nuxt-проксі
    // (див. CLAUDE.md "Архітектурні рішення" п.6)
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
