<?php

namespace Lighthart\GridBundle\Grid;


use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Cell {

    protected $attr;  // html attributes on <td>
    protected $title; // column reference
    protected $value; // the contents of the cell
    protected $tr; // which row we belong in

    public function __construct( $prop = array() ) {
        foreach ($prop as $k => $p) {
            $this->$k = $p;
        }
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

    public function getTitle() {
        return $this->title;
    }

    public function setTitle( $title ) {
        $this->title = $title;
        return $this;
    }

    public function getTr() {
        return $this->tr;
    }

    public function setTr( $tr ) {
        $this->tr = $tr;
        return $this;
    }

    public function td() {
        return "".
            "<".$this->type." class=\"".( $this->attr?:"" ).
            "\" data-role-lg-header=\"".
            ( $this->title?:"" )
            ."\">"
            .$this->value
            ."</".$this->type.">";
    }

    public function th(){
        return $this->td();
    }

}
