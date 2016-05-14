<?php
return array(
    'zf-versioning' => array(
        'default_version' => 1,
        'uri' => array(
            0 => 'ecommerce.rest.users',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'Ecommerce\\V1\\Rest\\Users\\UsersResource' => 'Ecommerce\\V1\\Rest\\Users\\UsersResourceFactory',
        ),
    ),
    'router' => array(
        'routes' => array(
            'ecommerce.rest.users' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/users[/:users_id]',
                    'defaults' => array(
                        'controller' => 'Ecommerce\\V1\\Rest\\Users\\Controller',
                    ),
                ),
            ),
        ),
    ),
    'zf-rest' => array(
        'Ecommerce\\V1\\Rest\\Users\\Controller' => array(
            'listener' => 'Ecommerce\\V1\\Rest\\Users\\UsersResource',
            'route_name' => 'ecommerce.rest.users',
            'route_identifier_name' => 'users_id',
            'collection_name' => 'users',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PATCH',
                2 => 'PUT',
                3 => 'DELETE',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'Ecommerce\\V1\\Rest\\Users\\UsersEntity',
            'collection_class' => 'Ecommerce\\V1\\Rest\\Users\\UsersCollection',
            'service_name' => 'Users',
        ),
    ),
    'zf-content-negotiation' => array(
        'controllers' => array(
            'Ecommerce\\V1\\Rest\\Users\\Controller' => 'HalJson',
        ),
        'accept_whitelist' => array(
            'Ecommerce\\V1\\Rest\\Users\\Controller' => array(
                0 => 'application/vnd.ecommerce.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
        ),
        'content_type_whitelist' => array(
            'Ecommerce\\V1\\Rest\\Users\\Controller' => array(
                0 => 'application/vnd.ecommerce.v1+json',
                1 => 'application/json',
            ),
        ),
    ),
    'zf-hal' => array(
        'metadata_map' => array(
            'Ecommerce\\V1\\Rest\\Users\\UsersEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'ecommerce.rest.users',
                'route_identifier_name' => 'users_id',
                'hydrator' => 'Zend\\Hydrator\\ArraySerializable',
            ),
            'Ecommerce\\V1\\Rest\\Users\\UsersCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'ecommerce.rest.users',
                'route_identifier_name' => 'users_id',
                'is_collection' => true,
            ),
        ),
    ),
    'zf-content-validation' => array(
        'Ecommerce\\V1\\Rest\\Users\\Controller' => array(
            'input_filter' => 'Ecommerce\\V1\\Rest\\Users\\Validator',
        ),
    ),
    'input_filter_specs' => array(
        'Ecommerce\\V1\\Rest\\Users\\Validator' => array(
            0 => array(
                'required' => true,
                'validators' => array(),
                'filters' => array(),
                'name' => 'username',
                'error_message' => 'Invalid username.',
            ),
            1 => array(
                'required' => true,
                'validators' => array(),
                'filters' => array(),
                'name' => 'firstname',
                'error_message' => 'Invalid firstname.',
            ),
            2 => array(
                'required' => true,
                'validators' => array(),
                'filters' => array(),
                'name' => 'lastname',
                'error_message' => 'Invalid lastname.',
            ),
        ),
    ),
);
