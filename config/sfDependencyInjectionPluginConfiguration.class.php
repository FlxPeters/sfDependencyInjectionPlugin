<?php

/**
 * Class sfDependencyInjectionPluginConfiguration
 */
class sfDependencyInjectionPluginConfiguration extends sfPluginConfiguration
{
    /**
     * @var ServiceContainerManager
     */
    protected $containerManager;

    /**
     * @see sfPluginConfiguration
     */
    public function initialize()
    {
        $this->dispatcher->connect('context.load_factories', array($this, 'loadContainer'));
        $this->dispatcher->connect('configuration.method_not_found', array($this, 'listenToMethodNotFound'));
        $this->dispatcher->connect('plugin.method_not_found', array($this, 'listenToMethodNotFound'));
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
        $this->containerManager = new ServiceContainerManager($this->dispatcher, $this->getLogger());
        $class = $this->getContainerClass();
        $container = $this->containerManager->initializeServiceContainer($class);
        $this->dispatcher->notify(new sfEvent($container, 'service_container.loaded'));

    }

    private function getLogger()
    {
        if (sfContext::hasInstance()) {
            return sfContext::getInstance()->getLogger();
        }
    }

    /**
     * Gets the container class.
     *
     * @return string The container class
     */
    protected function getContainerClass()
    {
        return (sfConfig::get('sf_debug') ? 'Debug' : '') . ucfirst(
            sfConfig::get('sf_environment')
        ) . 'ServiceContainer';
    }

    /**
     * Return the Container or a Service when calling a matching method
     *
     * @param $event sfEvent
     * @return bool
     */
    public function listenToMethodNotFound($event)
    {
        if ('getServiceContainer' == $event['method']) {
            $event->setReturnValue($this->containerManager->getServiceContainer());
            return true;
        }

        if ('getContainerBuilder' == $event['method']) {
            $event->setReturnValue($this->containerManager->getContainerBuilder());
            return true;
        }

        if ('getService' == $event['method']) {
            $event->setReturnValue($this->containerManager->getServiceContainer()->get($event['arguments'][0]));
            return true;
        }

        return false;
    }
}
