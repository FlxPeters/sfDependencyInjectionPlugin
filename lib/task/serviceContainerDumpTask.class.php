<?php

use Symfony\Component\DependencyInjection\Dumper\GraphvizDumper;

class ServiceContainerDumpTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(
            array(
                new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
                new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
                new sfCommandOption('output', null, sfCommandOption::PARAMETER_OPTIONAL, 'The output path', sfConfig::get('sf_data_dir') . '/container.dot')
            )
        );

        $this->namespace = 'serviceContainer';
        $this->name = 'dump';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [serviceContainer:dump|INFO] task dump generates a dot representation of the ServiceContainer.
Call it with:

  [php symfony serviceContainer:dump|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $this->configuration = sfProjectConfiguration::getApplicationConfiguration(
            $options['application'],
            $options['env'],
            true
        );

        sfContext::createInstance($this->configuration);

        $container = sfContext::getInstance()->getContainerBuilder();
        $dumper = new GraphvizDumper($container);
        $this->logSection('ServiceContainer', 'dump to ' . $options['output']);
        file_put_contents(sfConfig::get('sf_data_dir') . '/container.dot', $dumper->dump());
    }
}
