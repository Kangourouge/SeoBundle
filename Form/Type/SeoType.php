<?php

namespace KRG\CmsBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use KRG\CmsBundle\Entity\SeoInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class SeoType extends AbstractType
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('url', null, [
            'label'       => 'seo.url',
            'constraints' => $options['required_url'] ? new NotNull() : null,
            'required'    => $options['required_url'],
        ]);

        if ($options['metas']) {
            $builder
                ->add('metaTitle', TextType::class, [
                    'label' => 'seo.metaTitle',
                ])
                ->add('metaDescription', TextareaType::class, [
                    'label' => 'seo.metaDescription',
                ]);
        }

        if ($options['pre_content']) {
            $builder->add('preContent', ContentType::class, [
                'label'  => 'seo.preContent',
                'height' => 200,
            ]);
        }

        if ($options['post_content']) {
            $builder->add('postContent', ContentType::class, [
                'label'  => 'seo.postContent',
                'height' => 200,
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
    }

    public function onPreSetData(FormEvent $event)
    {
        $entity = $event->getData();

        if ($entity === null) {
            /* @var $seo SeoInterface */
            $classMetadata = $this->entityManager->getClassMetadata(SeoInterface::class);
            $seo = $classMetadata->getReflectionClass()->newInstanceArgs();
            $event->setData($seo);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class'   => SeoInterface::class,
            'required_url' => true,
            'pre_content'  => false,
            'post_content' => false,
            'title'        => false,
            'metas'        => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'krg_cms_seo';
    }
}
