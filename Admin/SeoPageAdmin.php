<?php

namespace KRG\SeoBundle\Admin;

use KRG\SeoBundle\Entity\RouteParameter;
use KRG\SeoBundle\Entity\SeoInterface;
use KRG\SeoBundle\Entity\SeoPageInterface;
use KRG\SeoBundle\Entity\SeoRoute;
use KRG\SeoBundle\Form\Admin\SeoPageSeoForm;
use KRG\SeoBundle\Form\DataTransformer\JsonToStringTransformer;
use KRG\SeoBundle\Form\SeoForm;
use KRG\SeoBundle\Form\SeoFormRegistry;
use Doctrine\ORM\EntityManager;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Routing\Router;
use Symfony\Component\Validator\Constraints\NotBlank;

class SeoPageAdmin extends Admin
{
    protected $baseRouteName = 'admin_app_seo_page';
    protected $baseRoutePattern = 'seo_page';

    /**
     * @var SeoInterface
     */
    private $seoClass;

    /**
     * @var $router Router
     */
    protected $router;

    /**
     * @var $seoFormRegistry SeoFormRegistry
     */
    private $seoFormRegistry;

    /**
     * @var $entityManager EntityManager
     */
    private $entityManager;

    protected $clearRoutingCache;

    protected function configureFormFields(FormMapper $formMapper)
    {
        /* @var $seoPage SeoPageInterface */
        $seoPage = $this->getSubject();
        $isNew = $seoPage->getId() === null;

        // Creation
        $formMapper
            ->add('formType', ChoiceType::class, array(
                'choices'   => $this->getAliasChoices(),
                'disabled'  => !$isNew,
                'read_only' => !$isNew,
                'required'  => false,
            ))
            ->add('url', TextType::class, array(
                'mapped'      => false,
                'constraints' => new NotBlank(),
            ))
            ->add('formData', TextareaType::class, array('data' => $this->request->query->get('parameters')))->get('formData')->addModelTransformer(new JsonToStringTransformer());
        ;

        // Edition
        if (!$isNew) {
            $formMapper
                ->remove('url')
                ->add('seo.url')
                ->add('preContent')
                ->add('postContent')
                ->add('seo.metaTitle')
                ->add('seo.metaDescription')
                ->add('seo.ogTitle')
                ->add('seo.ogDescription')
            ;
        }

        $formMapper->getFormBuilder()->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onPostSubmit'));
    }


    public function onPostSubmit(FormEvent $event)
    {
        /* @var $seoPage SeoPageInterface */
        $seoPage = $event->getData();
        $form = $event->getForm();
        $isNew = $seoPage->getId() === null;

        // Creation
        if ($isNew) {
            /* @var $seo SeoInterface */
            $seo = new $this->seoClass;
            $seo->setUrl($form->get('url')->getData());
            $seoPage->setSeo($seo);

            // Set form route, parameters
            if ($seoPage->getFormType()) {
                $service = $this->seoFormRegistry->get($seoPage->getFormType());
                $seoPage->setFormRoute($service['route']);
                $seoPage->setFormParameters(array());
            }

            // Flush to get id
            $this->entityManager->persist($seoPage);
            $this->entityManager->flush();

            // Fill Seo
            $seo->setRoute('krg_seo_page_show');
            $seo->setParameters(array('id' => $seoPage->getId()));

            $this->entityManager->flush();
        }

        $this->clearRoutingCache->exec();
    }

    private function getAliasChoices()
    {
        $choices = array();

        foreach ($this->seoFormRegistry->all() as $formType => $service) {
            $choices[$formType] = $service['alias'];
        }

        return $choices;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('formRoute')
            ->add('seo.url')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit'   => array(),
                    'delete' => array(),
                ),
            ));
    }

    /* */

    public function getTemplate($name)
    {
        if ($name === 'edit') {
            return 'KRGSeoBundle:Admin:Form/edit.html.twig';
        }

        return parent::getTemplate($name);
    }

    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    public function setSeoFormRegistry(SeoFormRegistry $seoFormRegistry)
    {
        $this->seoFormRegistry = $seoFormRegistry;
    }

    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setClearRoutingCache($clearRoutingCache)
    {
        $this->clearRoutingCache = $clearRoutingCache;
    }

    public function setSeoClass($seoClass)
    {
        $this->seoClass = $seoClass;
    }
}