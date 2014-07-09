<?php

namespace Lighthart\GridBundle\Grid;


use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Th extends Cell {

    public function __construct( $prop = array() ) {
        parent::__construct( $prop );
        foreach ( $prop as $k => $p ) {
            $this->$k = $p;
        }

        if ( !$this->value ) {
            $this->value = $this->title;
        }
        $this->type = 'th';
    }
}
