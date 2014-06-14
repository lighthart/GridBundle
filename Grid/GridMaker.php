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
    private $queryBuilder;
    private $grid;

    public function __toString() {
        return "Grid Maker -- Don't print this";
    }

    public function __construct( $doctrine ) {
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager();
        $this->grid = new Grid();
    }

    public function getRequest() {
        return $this->request;
    }

    public function setRequest( Request $request ) {
        $this->request = $request;
        return $this;
    }

    public function getGrid() {
        return $this->grid;
    }

    public function setGrid( Grid $grid ) {
        $this->grid = $grid;
        return $this;
    }

    public function newGrid( ) {
        $this->grid = new Grid();
        return $this;
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

    public function setQuery( $query ) {
        $this->query = $query ;
        return $this;
    }

    public function getQ() {
        return $this->getQuery();
    }

    public function setQ( $query ) {
        return $this->setQuery( $query );
    }


    public function getQueryBuilder() {
        return $this->queryBuilder ;
    }

    public function setQueryBuilder( $queryBuilder ) {
        $this->queryBuilder = $queryBuilder ;
        return $this;
    }

    public function getQB() {
        return $this->getQueryBuilder() ;
    }

    public function setQB( $queryBuilder ) {
        return $this->setQueryBuilder( $queryBuilder ) ;
    }

    public function addColumn( $entity, $value='id' ) {
        $this->getGrid()->addColumn( new Column( $entity, $value ) );
    }

    public function hydrateGridFromQB() {
        $this->mapColumnsFromQB();die;
        $results = $this->getQueryBuilder()->getQuery()->getResult( Query::HYDRATE_SCALAR );
        $this->getGrid()->fillTh( $results[0] ) ;
        $this->getGrid()->fillTr( $results ) ;
    }

    public function mapColumnsFromQB() {
        $qb = $this->getQB();
        foreach ( $qb->getDQLParts()['select'] as $select ) {
            $qb->select( 'partial '.$select->getParts()[0].'.{id}' );
        }


        $entities = array_merge(
            array_map( function( $f ) {
                    return $f->getAlias();
                }, $qb->getDQLPart('from')),
            array_map( function( $f ) {
                    return $f->getAlias();
                }, $qb->getDQLPart('join')[$qb->getDQLParts()['from'][0]->getAlias()] )
        );

        foreach ( $this->getGrid()->getColumns() as $entity => $field ) {
            if ( in_array( $entity, $entities ) ) {
                $partial = 'partial '.$entity.'.{'.$field.'}';
                $partials = array_map(function($s) { return $s->getParts()[0]; }, $qb->getDQLPart('select') );
                    var_dump('|partial '.$entity.'.{(.*?,)?'.$field.'(,.*?)?}|');
                    var_dump($partial);
                if (!preg_match('|partial '.$entity.'.{(.*?,)?'.$field.'(,.*?)?}|',$partial, $partials)) {
                    var_dump('no match');
                    // this needs to be adjusted to add fields to partial, instead of another entity reference
                    // pregmatch

                    $qb->addSelect($partial);
                }

            }
        }
        var_dump( $qb->getQuery()->getDQL() );
        die;
    }

}
