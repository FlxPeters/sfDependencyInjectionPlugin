<?php

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

/**
 * Class sfDependencyInjectionPluginConfiguration
 */
class sfDependencyInjectionPluginConfiguration extends sfPluginConfiguration
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @see sfPluginConfiguration
     */
    public function initialize()
    {
        $this->dispatcher->connect('context.load_factories', array($this, 'loadContainer'));

        $this->dispatcher->connect('configuration.method_not_found', array($this, 'listenToMethodNotFound'));
        $this->dispatcher->connect('component.method_not_found', array($this, 'listenToMethodNotFound'));
        $this->dispatcher->connect('context.method_not_found', array($this, 'listenToMethodNotFound'));
        $this->dispatcher->connect('view.method_not_found', array($this, 'listenToMethodNotFound'));
    }

    public function loadContainer(sfEvent $event)
    {
        if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled')) {
            $timer = sfTimerManager::getTimer('Initialize the ServiceContainer');
        }
        $this->initializeServiceContainer();

        if (sfConfig::get('sf_debug') && sfConfig::get('sf_logging_enabled')) {
            $timer->addTime();
        }
    }

    /**
     * Build a new Container or return a existing from cache
     */
    private function initializeServiceContainer()
    {
        $class = $this->getContainerClass();

        $cacheDir = (sfConfig::get('sf_app_cache_dir'))
            ? sfConfig::get('sf_app_cache_dir')
            : sfConfig::get('sf_cache_dir');

        $file = $cacheDir . '/' . $class . '.php';
        $containerConfigCache = new ConfigCache($file, sfConfig::get('sf_debug'));

        if (!$containerConfigCache->isFresh()) {
            sfContext::getInstance()->getLogger()->info('Load ServiceContainer from Config');

            $containerBuilder = $this->getContainerBuilder();

            $containerBuilder->compile();
            $dumper = new PhpDumper($containerBuilder);
            $containerConfigCache->write(
                $dumper->dump(array('class' => $class)),
                $containerBuilder->getResources()
            );
        } else {
            sfContext::getInstance()->getLogger()->info('Load ServiceContainer from Cache');
        }

        require_once $file;
        $this->container = new $class();

        $this->dispatcher->notify(new sfEvent($this->container, 'service_container.loaded'));
    }

    /**
     * Gets the container class.
     *
     * @return string The container class
     */
    protected function getContainerClass()
    {
        return (sfConfig::get('sf_debug') ? 'Debug' : '') . ucfirst(sfConfig::get('sf_environment')) . 'ServiceContainer';
    }

    /**
     * Return the Container or a Service when calling a matching method
     *
     * @param $event
     * @return bool
     */
    public function listenToMethodNotFound($event)
    {
        if ('getServiceContainer' == $event['method']) {
            $event->setReturnValue($this->getServiceContainer());
            return true;
        }

        if ('getContainerBuilder' == $event['method']) {
            $event->setReturnValue($this->getContainerBuilder());
            return true;
        }

        if ('getService' == $event['method']) {
            $event->setReturnValue($this->getServiceContainer()->get($event['arguments'][0]));
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getServiceContainer()
    {
        return $this->container;
    }

    /**
     * Gets the container's base class.
     *
     * All names except Container must be fully qualified.
     *
     * @return string
     */
    protected function getContainerBaseClass()
    {
        return 'Container';
    }

    /**
     * @return ContainerBuilder
     */
    protected function getContainerBuilder()
    {
        $containerBuilder = new ContainerBuilder();

        // notify service_container.load_configuration to load configurations
        $this->dispatcher->notify(new sfEvent($containerBuilder, 'service_container.load_configuration'));
        return $containerBuilder;
    }
}
