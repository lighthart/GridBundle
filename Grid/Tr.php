<?php

namespace Lighthart\GridBundle\Grid;


use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Tr {
    private $attr; // html attributes on <thead>
    private $cell; // array of th or td

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

    public function addCell( $cell ) {
        $this->cell[] = $cell;
        $cell->setTr($this);
        return $this;
    }

    public function addTh( $cell ) {
        $this->addCell($cell);
    }

    public function addTd( $cell ) {
        $this->addCell($cell);
    }

    public function removeCell( $cell ) {
        // not sure how to implement yet
        // $this->cell[] = $cell;
        // return $this;
    }

    public function setCell( array $cell ) {
        $this->cell = $cell;
        return $this;
    }

    public function tr() {
        return "".
            "<tr class=\"".( $this->attr?:"" )."\">"
            .implode( "",
            array_map(
                function( $field ) {
                    return $field->td();
                },
                $this->cell )
            )
            ."</tr>";
    }
}
