<?php

namespace Lighthart\GridBundle\Grid;

class Section
{
    private $attr; // html attributes on <thead>
    private $row;   // the tr's in the section
    private $table;
    protected $type;

    public function __construct()
    {
        $this->tr   = [];
        $this->attr = [];
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

    public function getAttr()
    {
        return $this->attr;
    }

    public function setAttr($attr)
    {
        $this->attr = $attr;

        return $this;
    }

    public function getRow()
    {
        return $this->row;
    }

    public function setRow($row)
    {
        $this->row = $row;

        return $this;
    }

    public function addRow($row)
    {
        $this->row[] = $row;

        return $this;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }
}
