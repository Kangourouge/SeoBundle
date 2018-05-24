<?php

namespace KRG\CmsBundle\Entity\Subscriber;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use KRG\CmsBundle\DependencyInjection\KRGCmsExtension;
use KRG\CmsBundle\Entity\SeoInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            EasyAdminEvents::POST_LIST_QUERY_BUILDER => ['onPostListQueryBuilder'],
        ];
    }

    public function onPostListQueryBuilder(GenericEvent $event)
    {
        $this->seoQueryBuilder($event);
    }

    protected function seoQueryBuilder(GenericEvent $event)
    {
        $entity = $event->getArgument('entity');

        if ($this->implementsInterface($entity['class'], SeoInterface::class)) {
            $queryBuilder = $event->getArgument('query_builder');

            $excludedUidStartsWith = [
                KRGCmsExtension::KRG_ROUTE_SEO_PAGE_PREFIX,
            ];

            $i = 0;
            foreach ($excludedUidStartsWith as $uidStartsWith) {
                $param = 'param_'.$i;
                $queryBuilder
                    ->andWhere('entity.uid NOT LIKE :'.$param)
                    ->setParameter($param, $uidStartsWith.'%');
            }
        }
    }

    protected function implementsInterface($class, $interface)
    {
        $classMetadata = $this->entityManager->getMetadataFactory()->getMetadataFor($class);

        return $classMetadata->getReflectionClass()->implementsInterface($interface);
    }
}