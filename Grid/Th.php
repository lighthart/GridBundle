<?php

namespace Lighthart\GridBundle\Grid;


use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;

class Th {

    private $title;     // column heading
    private $attr;      // html attributes on <th>

    public function __construct( ) {
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle( $title ) {
        $this->title = $title;
        return $this;
    }

    public function getAttr() {
        return $this->attr;
    }

    public function setAttr( $attr ) {
        $this->attr = $attr;
        return $this;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue( $value ) {
        $this->value = $value;
        return $this;
    }

    public function th() {
        return "<th class=\"".($this->attr?:"")."\">"
            .$this->value
            ."</th>";
    }
}
