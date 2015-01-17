<?php
namespace Lighthart\GridBundle\Grid;

class Action
{
    private $attr;
    private $icon;
    private $name;
    private $route;
    private $security;
    private $severity;
    private $columns;

    public function __construct($options = [])
    {
        // default action is always present
        // default severity is btn-default
        // default security is an anonymous function or a boolean
        // columns are fields used by security's function
        $options = array_merge([
            'attr'     => null,
            'icon'     => null,
            'name'     => null,
            'route'    => null,
            'security' => true,
            'severity' => 'btn-default',
            'title'    => null,
        ], $options);
        $this->attr     = $options['attr'];
        $this->icon     = ($options['icon']);
        $this->name     = $options['name'];
        $this->route    = $options['route'];
        $this->security = $options['security'];
        $this->severity = $options['severity'];
        $this->title    = $options['title'];
    }

    public function __toString()
    {
        return "Action " . $this->name . " printed.";
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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function setRoute($route)
    {
        $this->route = $route;

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

    public function getSeverity()
    {
        return $this->severity;
    }

    public function setSeverity($severity)
    {
        $this->severity = $severity;

        return $this;
    }
}
