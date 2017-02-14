<?php

namespace Application;

return [
    'service_manager' =>
        [
            'invokables' =>
                [
                    'entity.user' => '\Ecommerce\V1\Rest\Users\UsersEntity'
                ],
            'factories' =>
                [
                    'mapper.user'  => '\Ecommerce\V1\Rest\Users\UsersMapperFactory',
                    'service.user' => '\Ecommerce\V1\Rest\Users\UsersServiceFactory',
                ],
            'shared' =>
                [
                    'entity.user' => false
                ]
        ]
];