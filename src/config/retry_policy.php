<?php
return [
    'autoload'       => null,
    'current_driver' => env('RETRY_POLICY_DRIVER', 'kafka'),
    'drivers'        => [
        'kafka' => [
            'topic'   => env('KAFKA_RETRY_POLICY_TOPIC', 'default'),
            'brokers' => [
                env('KAFKA_BROKER_HOST', '127.0.0.1') . ':' . env('KAFKA_BROKER_PORT', 9092),
            ],
        ]
    ]
];
