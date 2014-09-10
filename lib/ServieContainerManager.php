<?php

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

class ServiceContainerManager
{
    private static $container;
    private $dispatcher;
    private $logger;

    /**
     * @param sfEventDispatcher $dispatcher
     * @param sfLoggerInterface $logger
     */
    public function __construct(sfEventDispatcher $dispatcher = null, $logger = null)
    {
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * @return ContainerBuilder
     */
    public static function getServiceContainer()
    {
        return self::$container;
    }

    public function initializeServiceContainer($className = 'serviceContainer')
    {
        $cacheDir = (sfConfig::get('sf_app_cache_dir'))
            ? sfConfig::get('sf_app_cache_dir')
            : sfConfig::get('sf_cache_dir');

        $file = $cacheDir . '/' . $className . '.php';
        $containerConfigCache = new ConfigCache($file, sfConfig::get('sf_debug'));

        if (!$containerConfigCache->isFresh()) {
            $this->log('Load ServiceContainer from Config');

            $containerBuilder = $this->getContainerBuilder();

            $containerBuilder->compile();
            $dumper = new PhpDumper($containerBuilder);
            $containerConfigCache->write(
                $dumper->dump(array('class' => $className)),
                $containerBuilder->getResources()
            );
        } else {
            $this->log('Load ServiceContainer from Cache');
        }

        require_once $file;
        self::$container = new $className();
        if ($this->dispatcher) {
            $this->dispatcher->notify(new sfEvent(self::$container, 'service_container.loaded'));
        }

        return self::$container;
    }

    private function log($log)
    {
        if ($this->logger) {
            $this->logger->info($log);
        }
    }

    /**
     * @return ContainerBuilder
     */
    public function getContainerBuilder()
    {
        $containerBuilder = new ContainerBuilder();

        if ($this->dispatcher) {
            // notify service_container.load_configuration to load configurations
            $this->dispatcher->notify(new sfEvent($containerBuilder, 'service_container.load_configuration'));
        }

        return $containerBuilder;
    }
}
