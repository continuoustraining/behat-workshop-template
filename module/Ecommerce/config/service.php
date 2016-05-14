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
                    'mapper.user' => '\Ecommerce\V1\Rest\Users\UsersMapperFactory'
                ],
            'shared' =>
                [
                    'entity.user' => false
                ]
        ]
];