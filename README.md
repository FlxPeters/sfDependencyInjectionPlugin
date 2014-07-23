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
        "fpeters/sf-dependency-injection-plugin": "1.*"
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

Todo
