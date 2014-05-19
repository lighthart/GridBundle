<?php

namespace Lighthart\GridBundle\Grid;


use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;

class Td {

    private $th;    // Which column
    private $attr;  // html attributes on <td>
    private $value; // the contents of the cell

    public function __construct( ) {
        $this->value  = array();
        $this->th = array();
    }

    public function getTh() {
        return $this->th;
    }

    public function setTh( $th ) {
        $this->th = $th;
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

    public function td() {
        return "<td class=\"".($this->attr?:"")."\">"
            .$this->value
            ."</td>";
    }
}
