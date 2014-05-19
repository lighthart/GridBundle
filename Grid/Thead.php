<?php

namespace Lighthart\GridBundle\Grid;


use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;

class Thead {

    private $attr; // html attributes on <thead>
    private $th;   // thead <th>

    public function __construct( ) {
        $this->th = array();
    }


    public function getAttr() {
        return $this->attr;
    }

    public function setAttr( $attr ) {
        $this->attr = $attr;
        return $this;
    }

    public function getTh() {
        return $this->th;
    }

    public function setTh( $th ) {
        $this->th = $th;
        return $this;
    }

    public function thead() {
        return "<thead class=\"".( $this->attr?:"" )."\">"
            .implode( '', array_map( function( $th ) { return $th->th(); }, $this->th ) )
            ."</thead>";
    }

}
