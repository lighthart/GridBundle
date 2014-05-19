<?php

namespace Lighthart\GridBundle\Grid;


use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;

class Tr {

    private $attr; // html attributes on <thead>
    private $cell;   // tr <cell>

    public function __construct( ) {
        $this->cell = array();
    }

    public function getAttr() {
        return $this->attr;
    }

    public function setAttr( $attr ) {
        $this->attr = $attr;
        return $this;
    }

    public function getCell() {
        return $this->cell;
    }

    public function setCell( $cell ) {
        $this->cell = $cell;
        return $this;
    }

    public function tr() {
        return "<tr class=\"".($this->attr?:"")."\">"
            .array_map( function( $field ) return {$field->cell();}, $this->cell );
            ."</tr>";
    }
}
