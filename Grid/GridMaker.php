<?php

namespace Lighthart\GridBundle\Grid;


// use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;

class GridMaker {

    private $doctrine;
    private $request;
    private $query;
    private $table;

    public function __toString() {
        return "Grid Maker -- Don't print this";
    }

    public function __construct( $doctrine ) {
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager();
    }

    public function verifyClass( String $class, $slash = null ) {
        // Default is class name is sent with backslashes
        // if another delimiter is used, for example '/' or '_'
        // Send as parameter

        if ( $slash ) {
            $backslash = str_replace( $slash, '\\', $class );
        }
        $metadataFactory = $em->getMetadataFactory();

        $error = '';

        if ( !$class ) {
            $error .= 'Class for grid verify not specified';
        }

        try {
            $metadata = $metadataFactory->getMetadataFor( $backslash );
        } catch ( \Exception $ex ) {
            $metadata=null;
            $error .= 'No metadata for class: '.$backslash;
        }

        if ( $error != '' ) {
            $error = 'grid.maker error: '.$error;
        }

        if ( $metadata ) {
            return
            array(
                'class'    => $class    ,
                'metadata' => $metadata ,
                'error'    => null      ,
            );
        } else {
            array(
                'class'    => null      ,
                'metadata' => null      ,
                'error'    => $error    ,
            );
        }
    }

    public function getQuery() {
        return $this->query;
    }

    public function setQuery( Query $query ) {
        $this->query = $query ;
        return $this;
    }

    public function getRequest() {
        return $this->request;
    }

    public function setRequest( Request $request ) {
        $this->request = $request;
        return $this;
    }

    public function getTable() {
        return $this->table;
    }

    public function setTable( $table ) {
        $this->table = $table;
        return $this;
    }

    public function newTable( ) {
        $this->table = new Table();
        return $this;
    }
}
