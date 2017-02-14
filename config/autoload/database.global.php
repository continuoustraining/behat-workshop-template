<?php
return [
    'doctrine' =>
        [
            'connection' =>
                [
                    'orm_default' =>
                        [
                            'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                            'params' =>
                                [
                                    'path' => __DIR__ . '/../../data/db/ecommerce.db'
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
                                    __DIR__ . '/../../module/Ecommerce/src/Ecommerce/V1/Rest/Users'
                                ]
                        ],
                    'orm_default' =>
                        [
                            'drivers' =>
                                [
                                    'Ecommerce' => 'Ecommerce_Driver',
                                ]
                        ]
                ]
        ]
];