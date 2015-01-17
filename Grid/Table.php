<?php

namespace Lighthart\GridBundle\Grid;

class Table
{
    private $attr;    // html attributes on <table>
    private $section; // table <thead> or table <tbody>
    private $grid;    // table <tbody>
    private $type;

    public function __construct($attr = [], $html = true)
    {
        foreach ($attr as $k => $p) {
            $this->$k = $p;
        }

        $this->section = [];

        if ($html) {
            $this->type      = 'table';
            $this->section[] = new Thead();
            $this->section[] = new Tbody();
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getGrid()
    {
        return $this->grid;
    }

    public function setGrid($grid)
    {
        $this->grid = $grid;

        return $this;
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

    public function getSection()
    {
        return $this->section;
    }

    public function setSection(Section $section)
    {
        $this->section = $section;

        return $this;
    }

    public function addSection()
    {
        $this->section = new Section();

        return $this;
    }

    public function getTbody()
    {
        $arr = array_filter($this->section, function ($s) { return 'tbody' == $s->getType(); });

        return array_pop($arr);
    }

    public function getThead()
    {
        $arr = array_filter($this->section, function ($s) { return 'thead' == $s->getType(); });

        return array_pop($arr);
    }
}
