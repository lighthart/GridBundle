<?php

namespace Lighthart\GridBundle\Grid;


use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Section {

    private $attr; // html attributes on <thead>
    private $tr;   // the tr's in the section
    private $table;

    public function __construct( ) {
        $this->tr = array();
    }


    public function getAttr() {
        return $this->attr;
    }

    public function setAttr( $attr ) {
        $this->attr = $attr;
        return $this;
    }

    public function getTr() {
        return $this->tr;
    }

    public function setTr( $tr ) {
        $this->tr = $tr;
        return $this;
    }

    public function addTr( $tr ) {
        $this->tr[] = $tr;
        return $this;
    }


    public function getTable() {
        return $this->table;
    }

    public function setTable( $table ) {
        $this->table = $table;
        return $this;
    }

    public function tbody() {
        return "".
            "<".$this->type.
            " class=\""
            .( $this->attr?:"" )
            ."\">"
            .implode( "\n", array_map( function( $tr ) { return $tr->tr(); }, $this->tr ) )
            ."</"
            .$this->type
            .">";
    }

    public function thead() {
        return $this->tbody();
    }
}
