<?php
namespace Ecommerce;

use Zend\Config\Config;
use ZF\Apigility\Provider\ApigilityProviderInterface;

class Module implements ApigilityProviderInterface
{
    public function getConfig()
    {
        $configPath = __DIR__ . '/../../config/';

        $configFiles =
            [
                'service.php'
            ];

        $config = new Config(require $configPath . 'module.config.php', true);

        foreach ($configFiles as $configFile) {
            $filePath = $configPath . $configFile;

            if (file_exists($filePath)) {
                $config->merge(new Config(require $filePath));
            }
        }

        return $config;
    }

    public function getAutoloaderConfig()
    {
        return array(
            'ZF\Apigility\Autoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }
}
