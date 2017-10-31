<?php

namespace KRG\SeoBundle\Entity;

interface BlockFormInterface extends BlockInterface
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
     * @return BlockFormInterface
     */
    public function setForm(array $form);

    /**
     * Get form
     *
     * @return array
     */
    public function getForm();
}
