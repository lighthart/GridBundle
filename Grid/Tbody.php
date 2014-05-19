<?php

namespace Lighthart\GridBundle\Grid;


use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;

class Tbody {

    private $attr; // html attributes on <th>
    private $tr;   // tbody <tr>

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

    public function tbody() {
        return "<tbody class=\"".( $this->attr?:"" )."\">"
            .implode( array_map( function( $tr ) { return $tr->tr(); }, $this->tr ) )
            ."</tbody>";
    }
}
