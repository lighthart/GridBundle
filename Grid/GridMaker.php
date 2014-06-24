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
        if ($this->query) {
            return $this->query;
        } else {
            return $this->queryBuilder->getQuery();
        }
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

    public function addField( $entity, $value='id' ) {
        $this->getGrid()->addColumn( new Column( $entity, $value ) );
    }

    public function addMethod($entity, $method=null) {
        if (method_exists($entity, $method)){

        }
    }

    public function hydrateGridFromQB() {
        // $this->mapFieldsFromQB();
        $this->mapMethodsFromQB();
        $results = $this->getQueryBuilder()->getQuery()->getResult( Query::HYDRATE_SCALAR );
        $this->getGrid()->fillTh( $results[0] ) ;
        $this->getGrid()->fillTr( $results ) ;
    }

    public function mapFieldsFromQB() {
        $qb = $this->getQB();

        $partials = [];
        foreach ( $qb->getDQLParts()['select'] as $select ) {
            if (preg_match('|partial (.*?)\.\{(.*?)\}|', $select->getParts()[0], $matches) ){
                $partials[$matches[1]][]=$matches[2];
            } else {
                $partials[$select->getParts()[0]] =array('id');
            }
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
            if (!isset($partials[$entity])) {
                $partials[$entity]=array('id');
            }

            if (in_array($field, $partials[$entity])) {
            } else {
                $partials[$entity][]=$field;
                // should probably add some stuff here to verify versus ORM data
                // in the mean time developers can be careful
            }
        }

        foreach ($partials as $entity => $fields){
            if ($qb->getRootAlias() == $entity){
                $qb->select( 'partial '.$entity.'.{'.implode(',', $fields).'}' );
            } else {
                $qb->addSelect( 'partial '.$entity.'.{'.implode(',', $fields).'}' );
            }
        }
    }

    public function mapMethodsFromQB() {
    }

}
