<?php
namespace Lighthart\GridBundle\Grid;

use Doctrine\ORM\Query;

class Column
{
    private $alias;
     // from the query
    private $value;
     // from the query
    private $options;

    public function __construct($alias, $value = 'id', array $options = [])
    {
        $this->alias   = $alias;
        $this->value   = $value;
        $options = array_merge([
            'security' => true,
        ], $options);
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

    public function getOptions($option = null)
    {
        if ($option === null) {
            return $this->options;
        } else {
            return $this->getOption($option);
        }
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

    public function setSecurity($security)
    {
        $this->options['security'] = $security;

        return $this;
    }

    public function getSecurity()
    {
        return $this->options['security'];
    }

}
