<?php
namespace Lighthart\GridBundle\Grid;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;

class Status
{

    private $attr;
    private $icon;
    private $security;
    private $title;

    public function __construct($options = array())
    {
        $options = array_merge(array(
            'attr' => null,
            'icon' => null,
            'route' => null,
            'security' => null,
            'title' => null,
        ) , $options);
        $this->attr = $options['attr'];
        $this->icon = $options['icon'];
        $this->security = $options['security'];
        $this->title = $options['title'];

        // default is action is always present
        $this->security = ($this->security === null ? $this->security : true);
    }

    public function __toString()
    {
        return "Status " . $this->title . " printed.";
    }

    public function getAttr()
    {
        return $this->attr;
    }

    public function setAttr($attr)
    {
        $this->attr = $attr;
        return $this;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    public function getSecurity()
    {
        return $this->security;
    }

    public function setSecurity($security)
    {
        $this->security = $security;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
}