<?php

namespace KRG\SeoBundle\Twig;

use Doctrine\ORM\EntityManagerInterface;
use KRG\SeoBundle\Entity\BlockFormInterface;
use KRG\SeoBundle\Entity\BlockInterface;
use KRG\SeoBundle\Entity\BlockStaticInterface;
use Symfony\Bridge\Twig\Extension\HttpKernelExtension;
use Twig\Node\Expression\BlockReferenceExpression;

/**
 * Class BlockExtension
 * @package KRG\SeoBundle\Twig
 */
class BlockExtension extends \Twig_Extension
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var string
     */
    private $cacheDirKrg;

    /**
     * @var string
     */
    private $cacheFileName;

    /**
     * @var \Twig_TemplateWrapper
     */
    private $template;

    /**
     * BlockExtension constructor.
     * @param EntityManagerInterface $entityManager
     * @param $cacheDir
     */
    public function __construct(EntityManagerInterface $entityManager, $cacheDir)
    {
        $this->entityManager = $entityManager;
        $this->cacheDir = $cacheDir;
        $this->cacheDirKrg = $cacheDir.'/krg';
        $this->cacheFileName = 'krg_seo_blocks.html.twig';
    }

    /**
     * Build blocks into a specific template
     *
     * @param \Twig_Environment $environment
     * @return \Twig_TemplateWrapper
     */
    private function getTemplateFromCache(\Twig_Environment $environment)
    {

        if ($this->template) {
            return $this->template;
        }

        $this->createFileTemplate();
        $environment->setCache($this->cacheDir);
        $environment->setLoader(new \Twig_Loader_Chain([
            $environment->getLoader(), // Preserve old loader
            new \Twig_Loader_Filesystem([$this->cacheDirKrg]) // Add KRG cache dir
        ]));

        $this->template = $environment->load($this->cacheFileName); // Load template from cache

        return $this->template;
    }

    /**
     * Generate $this->cacheFileName twig template composed of each blocks
     */
    public function createFileTemplate()
    {
        if (!is_dir($this->cacheDirKrg)) {
            mkdir($this->cacheDirKrg);
        }

        $path = sprintf('%s/%s', $this->cacheDirKrg, $this->cacheFileName);
        if (!file_exists($path)) {
            $blocksStatic = $this->entityManager->getRepository(BlockStaticInterface::class)->findAll();
            $blocksForm = $this->entityManager->getRepository(BlockFormInterface::class)->findAll();

            $content = [];
            /* @var $block BlockStaticInterface */
            foreach ($blocksStatic as $blockStatic) {
                if (false === $this->hasBlockLoop($blockStatic)) {
                    $content[] = sprintf("{%% block %s %%}%s{%% endblock %%}\n", $blockStatic->getKey(), $blockStatic->getContent());
                }
            }

            /* @var $blockForm BlockFormInterface */
            foreach ($blocksForm as $blockForm) {
                $content[] = sprintf("{%% block %s %%}{{ render(controller('KRGSeoBundle:Block:form', {'blockForm': %d})) }}{%% endblock %%}\n", $blockForm->getKey(), $blockForm->getId());
            }

            return (bool)file_put_contents($path, implode('', $content));
        }

        return false;
    }

    /**
     * Render a block by its key
     *
     * @param \Twig_Environment $environment
     * @param $key
     */
    public function getBlock(\Twig_Environment $environment, $key)
    {
        $template = $this->getTemplateFromCache($environment);

        if ($template->hasBlock($key)) {
            echo $template->renderBlock($key);
        }
    }

    /**
     * Simple block loop detect, can be improved
     *
     * @param BlockInterface $block
     * @return int
     */
    protected function hasBlockLoop(BlockInterface $block)
    {
        return (bool)strpos($block->getContent(), sprintf("block('%s')", $block->getKey()));
    }

    public function getFunctions()
    {
        return [
            'krg_block' => new \Twig_SimpleFunction('krg_block', array($this, 'getBlock'), [
                'needs_environment' => true, // Tell twig we need the environment
                'is_safe' => ['html'],
            ]),
        ];
    }
}
