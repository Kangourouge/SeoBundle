<?php

namespace KRG\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use KRG\CmsBundle\Entity\Validator\UniqueKey;
use KRG\CmsBundle\Entity\Validator\ValidContent;

/**
 * Block
 *
 * @ORM\MappedSuperclass()
 * @ValidContent()
 * @UniqueKey()
 * @Gedmo\Loggable
 */
class Block extends AbstractBlock implements BlockInterface, BlockContentInterface, Translatable
{
    /**
     * @Gedmo\Translatable()
     * @Gedmo\Versioned
     * @ORM\Column(type="html", nullable=true)
     */
    protected $content;

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        $this->content = htmlspecialchars_decode($content, ENT_QUOTES);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return (string)$this->content;
    }
}
