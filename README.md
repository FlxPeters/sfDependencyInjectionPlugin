sfDependencyInjectionPlugin
===========================

It provides supporting the Symfony's DependencyInjection component in your older symfony (1.4) project with Composer.

This Plugin is inspired by https://github.com/issei-m/sfDependencyInjectionPlugin but uses some other approach to load the configuration files.

Installation
------------

Create the following `composer.json` in your symfony 1.4 project's root.

```json
{
    "config": {
        "vendor-dir": "lib/vendor"
    },
    "require": {
        "fpeters/symfony-dependency-injection-plugin": "dev-master"
    },
    "autoload": {
        "psr-0": { "": "psr" }
    },
}
```

Here, Composer would install the plugin in your `plugins` directory and some Symfony2 components into `vendor/symfony/`.
Also, You can locate your PSR supported libraries to be auto-loaded in `%SF_ROOT%/psr` (optional).

Install the Composer and install some libraries.

```
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar install
```

To register the autoloader for libraries installed with composer, you must add this at the top of your ProjectConfiguration class:

``` php
# config/ProjectConfiguration.class.php

// Composer autoload
require_once dirname(__DIR__).'/lib/vendor/autoload.php';

// symfony1 autoload
require_once dirname(__DIR__).'/lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
    // ...
}
```

Usage
-----

The Plugin only creates an empty ContainerBuilder object.To add your service configuration, simply listen for the service_container.load_configuration event in your ProjectConfiguration. The following example will load the services.yml from the global config directory
 
``` php
// Load Services from services.yml
$this->dispatcher->connect(
    'service_container.load_configuration',
    function (sfEvent $event) {
        // load  global config dir
        $loader = new YamlFileLoader($event->getSubject(), new FileLocator(sfConfig::get('sf_config_dir')));
        $loader->load('services.yml');
    }
);

$this->dispatcher->connect(
    'service_container.loaded',
    function (sfEvent $event) {
        // here you can do stuff when container is loaded
        $service = $event->getSubject()->get('myCoolService');
    }
);
```

You can do nearly anything with this ContainerBuilder. So maybe have a look at documentation: http://symfony.com/doc/current/components/dependency_injection/compilation.html

To use a Service in your Code, simply call the Service Container.
```php

// in a action.class
$container = $this->getServiceContainer();
$container->get('myCoolService');

// there is a short version of that
$this->getService('myCoolService');

```
This will also work in your Templates anc Components.
```php
<?= $this->getService('myCoolService')->doStuff(); ?>
```
