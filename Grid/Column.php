<?php
namespace Lighthart\GridBundle\Grid;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;

class Column
{

    private $alias;
     // from the query
    private $value;
     // from the query
    private $options;

    public function __construct($alias, $value = 'id', array $options = array())
    {
        $this->alias = $alias;
        $this->value = $value;
        $this->options = $options;
    }

    public function __toString()
    {
        return "Column " . $this->alias . " printed.";
    }

    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function getEntity()
    {
        return stristr($this->alias, '_', true);
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    public function getOption($option)
    {
        return isset($this->options[$option]) ? $this->options[$option] : null;
    }
}
