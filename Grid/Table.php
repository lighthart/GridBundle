<?php

namespace Lighthart\GridBundle\Grid;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;

class Table {

    private $attr;   // html attributes on <table>
    private $thead;  // table <thead>
    private $tbody;  // table <tbody>
    private $grid;  // table <tbody>

    public function __construct( $prop = array() ) {
        foreach ($prop as $k => $p) {
            $this->$k = $p;
        }

        $this->thead = new Thead();
        $this->thead->setTable($this);
        $this->tbody = new Tbody();
        $this->tbody->setTable($this);
    }

    public function getGrid() {
        return $this->grid;
    }

    public function setGrid( $grid ) {
        $this->grid = $grid;
        return $this;
    }

    public function getAttr() {
        return $this->attr;
    }

    public function setAttr( $attr ) {
        $this->attr = $attr;
        return $this;
    }

    public function getThead() {
        return $this->thead;
    }

    public function setThead( Thead $thead ) {
        $this->thead = $thead;
        return $this;
    }

    public function newThead( ) {
        $this->thead = new Thead();
        return $this;
    }

    public function getTbody() {
        return $this->tbody;
    }

    public function setTbody( Tbody $tbody ) {
        $this->tbody = $tbody;
        return $this;
    }

    public function newTbody( ) {
        $this->tbody = new Tbody();
        return $this;
    }

    public function table() {
        return "<table class=\"".($this->attr?:"")."\">"
            .($this->getThead()?$this->getThead()->thead():"")
            .($this->getTbody()?$this->getTbody()->tbody():"")
            ."</table>";
    }
}
