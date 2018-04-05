<?php

namespace KRG\CmsBundle\Form\DataTransformer;

use AppBundle\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use EMC\FileinputBundle\Entity\FileInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class Base64DataTransformer implements DataTransformerInterface
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * Base64Type constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value === null || !($value instanceof FileInterface)) {
           return null;
        }
        return $value;
    }
    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if ($value === null || strlen($value) === 0) {
            return null;
        }

        if (preg_match('`data:image/png;base64,.+`', $value)) {
            return File::createFromBase64($value);
        }

        /**
         * search file from repository
         */

        $repository = $this->entityManager->getRepository(FileInterface::class);
        $image = $repository->findOneBy(['path' => '.'.$value]);
        if($image)
            return $image;

        throw new TransformationFailedException();

    }

}
