<?php
namespace Lighthart\GridBundle\Grid;

class Cell
{
    protected $attr; // html attributes on <td>
    private $options;
    protected $row; // which row we belong in
    private $type; // td or th
    protected $title; // column reference
    protected $value; // the contents of the cell

    public function __construct($prop = [])
    {
        foreach ($prop as $k => $p) {
            $this->$k = $p;
        }
    }

    public function getAttr()
    {
        // return $this->attr;

        // instead automatically add lg for general formatting on each cell

        $attr          = $this->attr;
        $attr['class'] = ((isset($attr['class']) && $attr['class']) ? $attr['class'] . ' lg' : 'lg');

        return $attr;
    }

    public function setAttr($attr)
    {
        $this->attr = $attr;

        return $this;
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

    public function getRow()
    {
        return $this->tr;
    }

    public function setRow($row)
    {
        $this->tr = $row;

        return $this;
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

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;

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

    // move this to view portion

    public function td()
    {
        return "" . "<" . $this->type . " class=\"" . ($this->attr ?: "") . "\" data-role-lg-header=\"" . ($this->title ?: "") . "\">" . ((is_object($this->value) && 'DateTime' == get_class($this->value)) ? $this->value->format('Y-m-d') : $this->value) . "</" . $this->type . ">";
    }

    public function th()
    {
        return $this->td();
    }
}
