<?php

namespace KRG\CmsBundle\Routing;

use KRG\CmsBundle\Entity\SeoInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class SeoListener
{
    /** @var RouterInterface */
    private $router;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(RouterInterface $router, EntityManagerInterface $entityManager)
    {
        $this->router = $router;
        $this->entityManager = $entityManager;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $routeName = $request->get('_route');
        if ($canonicalRoute = $request->attributes->get('_canonical_route')) {
            $routeName = $canonicalRoute;
        }

        /* @var $seo SeoInterface */
        $seo = $this->entityManager->getRepository(SeoInterface::class)->findOneByUid($routeName);
        if ($seo === null) {
            return null;
        }

        // Update request to keep url intact
        $route = $this->router->getRouteCollection()->get($seo->getRouteName());
        $request->attributes->set('_controller', $route->getDefault('_controller'));
        $request->attributes->set('_route', $seo->getRouteName());
        $request->attributes->set('_seo', $seo);

        $routeParams = [];
        foreach ($seo->getRouteParams() as $parameter => $value) {
            $routeParams[$parameter] = null === $value ? $request->attributes->get($parameter) : $value;
        }
        $request->attributes->set('_route_params', $routeParams);

        foreach ($seo->getRouteParams() as $key => $value) {
            if ($value) {
                $request->attributes->set($key, $value);
            }
        }
    }
}
