<?php

namespace Lighthart\GridBundle\Grid;


// use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;

class Grid {

    private $columns;
    private $table;

    public function __toString() {
        return "Grid -- Don't print this -- print the table instead";
    }

    public function __construct( ) {
        $this->columns = array();
        $this->table = new Table( array( 'attr' => 'table table-bordered table-condensed table-hover table-striped grid' ) );
        $this->table->setGrid( $this );
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

    public function addColumn( Column $column ) {
        $this->columns[$column->getEntity()] = $column->getValue();
        return $this;
    }

    public function removeColumn( Column $column ) {
        // not sure how to implement yet
        // $this->columns[] = $columns;
        // return $this;
    }

    public function setColumns( array $columns ) {
        foreach ($columns as $column) {
            $this->addColumn($column);
        }
        return $this;
    }

    public function getColumns(){
        return $this->columns;
    }

    public function getOrder(){
        return array_keys($this->columns);
    }

    public function newColumns( ) {
        $this->columns = new Columns();
        return $this;
    }

    public function orderColumns() {
        $thead = $this->getTable()->getThead();
        $tr = new Tr();
        array_map(
            function( $col ) use ( &$tr ) {
                $th = new Th( array( 'title' => $col ) );
                $tr->addTh( $th );
            },
            $this->getColumns()->getOrder()
        );
        $thead->addTr( $tr );
    }

    public function fillTh( array $result = array() ) {
        $thead = $this->getTable()->getThead();
        $columns = $this->getColumns();
        $tr = new Tr();
        foreach ( $result as $key => $value ) {
            $th = new Th(
                array(
                    'title' => $key
                )
            );
            $tr->addCell( $th );
            $this->addColumn( new Column($key) );
        }
        $thead->addTr( $tr );
    }

    public function fillTr( array $results = array() ) {
        $tbody = $this->getTable()->getTbody();
        $columns = $this->getColumns();
        foreach ( $results as $row => $result ) {
            $tr = new Tr();
            foreach ( $result as $key => $value ) {
                $td = new Td(
                    array(
                        'value' => $value,
                        'title' => $columns[$key]
                    )
                );
                $tr->addCell( $td );
            }
            $tbody->addTr( $tr );
        }
    }
}
