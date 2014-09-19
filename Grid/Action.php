<?php
namespace Lighthart\GridBundle\Grid;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;

class Action
{

    private $route;
    private $alias;

    public function __construct($alias, $route)
    {
        $this->alias = $alias;
        $this->route = $route;
    }

    public function __toString()
    {
        return "Action " . $this->alias . " printed.";
    }

    public function getAlias() {
         return $this->alias;
    }

    public function setAlias( $alias ) {
        $this->alias = $alias;
        return $this;
    }

    public function getRoute() {
         return $this->route;
    }

    public function setRoute( $route ) {
        $this->route = $route;
        return $this;
    }
}
