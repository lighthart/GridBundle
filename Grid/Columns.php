<?php

namespace Lighthart\GridBundle\Grid;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;

class Columns {

    private $columns; // array of th or td

    public function __construct( ) {
        $this->columns = array();
    }

    public function addColumn( $column ) {
        $this->columns[$column] = $column;
        return $this;
    }

    public function removeColumn( $column ) {
        // not sure how to implement yet
        // $this->columns[] = $columns;
        // return $this;
    }

    public function setColumns( array $columns ) {
        $this->columns = $columns;
        return $this;
    }

    public function getOrder(){
        return $this->columns;
    }
}
