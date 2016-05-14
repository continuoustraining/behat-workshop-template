<?php
return [
    'doctrine' =>
        [
            'connection' =>
                [
                    'orm_default' =>
                        [
                            'driverClass' => 'Doctrine\DBAL\Driver\PDOSqlite\Driver',
                            'params' =>
                                [
                                    'path' => __DIR__ . '/../../data/ecommerce.db'
                                ]
                        ]
                ],
            'driver' =>
                [
                    'Ecommerce_Driver' =>
                        [
                            'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                            'cache' => 'array',
                            'paths' =>
                                [
                                    __DIR__ . '/../../module/Ecommerce/src/Ecommerce/V1'
                                ]
                        ],
                    'orm_default' =>
                        [
                            'drivers' =>
                                [
                                    'App' => 'Ecommerce_Driver',
                                ]
                        ]
                ],
        ]
];