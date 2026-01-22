<?php

return [ 
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins' => ['http://localhost:3003', 'http://localhost:3000', 'http://erp.mimalmadinah.com', 'https://erp.mimalmadinah.com','https://www.mimalmadinah.com','https://mimalmadinah.com','http://localhost:5173'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 86400, 
    'supports_credentials' => true,

];
