<?php

namespace KRG\CmsBundle\Entity;

interface FilterInterface extends BlockInterface
{
    /**
     * @return string
     */
    public function getFormType();

    /**
     * @return array|null
     */
    public function getFormData();

    /**
     * Set form
     *
     * @param array $form
     *
     * @return FilterInterface
     */
    public function setForm(array $form);

    /**
     * Get form data compatible with form->submit
     *
     * @return array
     */
    public function getPureFormData();

    /**
     * Get form
     *
     * @return array
     */
    public function getForm();

    /**
     * Set seo
     *
     * @param SeoInterface $seo
     *
     * @return FilterInterface
     */
    public function setSeo(SeoInterface $seo = null);

    /**
     * Get seo
     *
     * @return SeoInterface
     */
    public function getSeo();
}
