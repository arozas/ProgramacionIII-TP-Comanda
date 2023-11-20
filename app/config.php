<?php
require_once './enums/UserType.php';

return [
    'user_types' => [
        'socio' => [
            'allowed_routes' => [
                'GET:/app/users/',
                'GET:/app/users/{id}',
                'POST:/app/users/',
                'PUT:/app/users/{id}',
                'DELETE:/app/users/{id}',

                'GET:/app/products/',
                'GET:/app/products/{id}',
                'POST:/app/products/',
                'PUT:/app/products/{id}',
                'DELETE:/app/products/{id}',

                'GET:/app/tables/',
                'GET:/app/tables/{id}',
                'POST:/app/tables/',
                'PUT:/app/tables/{id}',
                'DELETE:/app/tables/{id}',

                'GET:/app/orders/',
                'GET:/app/orders/{id}',
                'POST:/app/orders/',
                'PUT:/app/orders/{id}',
                'DELETE:/app/orders/{id}',
            ],
        ],
        'mozo' => [
            'allowed_routes' => [
                'GET:/app/tables/',
                'GET:/app/tables/{id}',
                'POST:/app/tables/',
                'PUT:/app/tables/{id}',

                'GET:/app/orders/',
                'GET:/app/orders/{id}',
                'POST:/app/orders/',
                'PUT:/app/orders/{id}',
            ],
        ],
        'bartender' => [
            'allowed_routes' => [
                'GET:/app/orders/',
                'GET:/app/orders/{id}',
            ],
        ],
        'cervecero' => [
            'allowed_routes' => [
                'GET:/app/orders/',
                'GET:/app/orders/{id}',
            ],
        ],
        'cocinero' => [
            'allowed_routes' => [
                'GET:/app/orders/',
                'GET:/app/orders/{id}',
            ],
        ],
        // Agregar más tipos de usuarios...
    ],
];

