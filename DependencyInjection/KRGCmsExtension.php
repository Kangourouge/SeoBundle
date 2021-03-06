<?php

namespace KRG\CmsBundle\DependencyInjection;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class KRGCmsExtension extends Extension implements PrependExtensionInterface
{
    const KRG_ROUTE_SEO_PREFIX = 'krg_seo_';
    const KRG_ROUTE_SEO_PAGE_PREFIX = self::KRG_ROUTE_SEO_PREFIX.'page_show_';
    const KRG_ROUTE_SEO_FILTER_PREFIX = self::KRG_ROUTE_SEO_PREFIX.'filter_show_';

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('krg_cms.seo', $config['seo'] ?? []);
        $container->setParameter('krg_cms.page', $config['page'] ?? []);
        $container->setParameter('krg_cms.blocks', $this->loadBlocks($config));

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('krg_cms.intl_locales', $container->hasParameter('krg_intl_locales') ? $container->getParameter('krg_intl_locales') : []);

        if (false === interface_exists('KRG\EasyAdminExtensionBundle\Toolbar\ToolbarInterface')) {
            foreach ($container->getDefinitions() as $key => $definition) {
                if (strstr($key, 'KRG\CmsBundle\Toolbar')) {
                    $container->removeDefinition($key);
                }
            }
        }
    }

    private function loadBlocks($config)
    {
        $blocks = [];

        if (isset($config['blocks_path'])) {
            $finder = new Finder();
            foreach ($config['blocks_path'] as $path) {
                if (is_dir($path)) {
                    $finder->files()->in($path)->name('*.yml');
                    foreach ($finder as $file) {
                        $this->loadBlockConfig($file, $blocks);
                    }
                } else {
                    $this->loadBlockConfig($path, $blocks);
                }
            }
        }

        return $blocks;
    }

    private function loadBlockConfig($file, &$blocks = [])
    {
        foreach (Yaml::parseFile($file) as $key => $yml) {
            if (isset($yml['template'])) {
                $blocks[$key] = $yml;
            }
        }

        return $blocks;
    }

    public function getAlias()
    {
        return 'krg_cms';
    }

    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('twig', [
            'form_themes' => [
                'KRGCmsBundle:Form:route.html.twig',
                'KRGCmsBundle:Form:filter.html.twig',
                'KRGCmsBundle:Form:html.html.twig',
                'KRGCmsBundle:Form:seo.html.twig',
                'KRGCmsBundle:Form:url.html.twig',
                'KRGCmsBundle:Form:base64file.html.twig',
            ]
        ]);
    }
}
