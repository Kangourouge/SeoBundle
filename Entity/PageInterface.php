<?php

namespace KRG\CmsBundle\Entity;

interface PageInterface extends BlockInterface
{
    /**
     * Set seo
     *
     * @param SeoInterface $seo
     *
     * @return PageInterface
     */
    public function setSeo(SeoInterface $seo = null);

    /**
     * Get seo
     *
     * @return SeoInterface
     */
    public function getSeo();
}
