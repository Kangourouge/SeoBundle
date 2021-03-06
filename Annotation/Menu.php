<?php

namespace KRG\CmsBundle\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Menu
{
    /** @var string */
    private $name;

    /** @var string */
    private $route;

    /** @var string */
    private $url;

    /** @var array */
    private $params;

    public function __construct(array $data)
    {
        $this->name = $data['value'] ?? null;
        $this->route = $data['route'] ?? null;
        $this->url = $data['url'] ?? null;
        $this->params = $data['params'] ?? [];
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setRoute(string $route)
    {
        $this->route = $route;

        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl(string $url = null)
    {
        $this->url = $url;

        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }
}
