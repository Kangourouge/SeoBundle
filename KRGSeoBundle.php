<?php

namespace KRG\SeoBundle;

use KRG\SeoBundle\DependencyInjection\Compiler\PageFormCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KRGSeoBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new PageFormCompilerPass());
    }
}
