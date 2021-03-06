<?php

namespace Lighthart\GridBundle\Grid;

class Row
{
    private $attr; // html attributes on <thead>
    private $cell; // array of th or td
    private $type;

    public function __construct($prop = [])
    {
        foreach ($prop as $k => $p) {
            $this->$k = $p;
        }
        $this->cell = [];
    }

    public function gettype()
    {
        return $this->type;
    }

    public function settype($type)
    {
        $this->type = $type;

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

    public function getCell()
    {
        return $this->cell;
    }

    public function addCell($cell)
    {
        $this->cell[] = $cell;
        $cell->setRow($this);

        return $this;
    }

    public function removeCell($cell)
    {
        // not sure how to implement yet
        // $this->cell[] = $cell;
        // return $this;
    }

    public function setCell(array $cell)
    {
        $this->cell = $cell;

        return $this;
    }
}
