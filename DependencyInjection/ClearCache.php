<?php

namespace KRG\CmsBundle\DependencyInjection;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class ClearCache
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $cacheDir;

    public function __construct(RouterInterface $router, Filesystem $filesystem, $cacheDir)
    {
        $this->router = $router;
        $this->filesystem = $filesystem;
        $this->cacheDir = $cacheDir;
    }

    public function warmupRouting()
    {
        foreach (array('matcher_cache_class', 'generator_cache_class') as $option) {
            $className = $this->router->getOption($option);
            $cacheFile = $this->cacheDir . DIRECTORY_SEPARATOR . $className . '.php';
            $this->filesystem->remove($cacheFile);
        }

        $cache = new FilesystemAdapter('seo');
        $cache->clear();

        $this->router->warmUp($this->cacheDir);
    }

    public function warmupTwig()
    {
        $this->filesystem->remove($this->cacheDir . '/krg');
    }
}