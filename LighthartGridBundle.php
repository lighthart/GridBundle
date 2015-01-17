<?php

namespace Lighthart\GridBundle;

use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LighthartGridBundle extends Bundle
{
    public function registerCommands(Application $application)
    {
        parent::registerCommands($application);
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}
