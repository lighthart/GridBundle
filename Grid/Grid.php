<?php
namespace Lighthart\GridBundle\Grid;

// use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;

class Grid
{

    private $columns;
    private $table;

    public function __toString()
    {
        return "Grid -- Don't print this -- print the table instead";
    }

    public function __construct(array $options = array())
    {
        $this->columns = array();
        $this->table = new Table($options);
        $this->table->setGrid($this);
    }

    public function verifyClass(String $class, $slash = null)
    {

        // Default is class name is sent with backslashes
        // if another delimiter is used, for example '/' or '_'
        // Send as parameter

        if ($slash) {
            $backslash = str_replace($slash, '\\', $class);
        }
        $metadataFactory = $em->getMetadataFactory();

        $error = '';

        if (!$class) {
            $error.= 'Class for grid verify not specified';
        }

        try {
            $metadata = $metadataFactory->getMetadataFor($backslash);
        }
        catch(\Exception $ex) {
            $metadata = null;
            $error.= 'No metadata for class: ' . $backslash;
        }

        if ($error != '') {
            $error = 'grid.maker error: ' . $error;
        }

        if ($metadata) {
            return array(
                'class' => $class,
                'metadata' => $metadata,
                'error' => null,
            );
        } else {
            array(
                'class' => null,
                'metadata' => null,
                'error' => $error,
            );
        }
    }

    public function getTable()
    {
        return $this->table;
    }

    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    public function newTable()
    {
        $this->table = new Table();
        return $this;
    }

    public function addColumn(Column $column)
    {
        $this->columns[$column->getAlias() ] = $column;
        return $this;
    }

    public function removeColumn(Column $column)
    {

        // not sure how to implement yet
        // $this->columns[] = $columns;
        // return $this;


    }

    public function setColumns(array $columns)
    {

        // columns should obviously be of type Column
        foreach ($this->columns as $k => $col) {
            unset($this->columns[$k]);
        }
        foreach ($columns as $column) {
            $this->addColumn($column);
        }
        return $this;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getOrder()
    {
        return array_keys($this->columns);
    }

    public function newColumns()
    {

        // clear them out to rerun
        $this->columns = [];
        return $this;
    }

    public function orderColumns()
    {
        $thead = $this->getTable()->getThead();
        $tr = new Tr();
        array_map(function ($col) use (&$tr)
        {
            $th = new Th(array(
                'title' => $col
            ));
            $tr->addTh($th);
        }
        , $this->getColumns()->getOrder());
        $thead->addTr($tr);
    }

    public function columnOptions(Column $column)
    {
    }

    public function addMethod(Column $column)
    {
        $column->setOptions(array_merge($column->getOptions() , array(
            'method' => true
        )));
        $this->columns[$column->getAlias() ] = $column;
        return $this;
    }

    public function fillTh(array $result = array())
    {
        if (array() != $result) {
            $result = $result[0];
        }
        $thead = $this->getTable()->getThead();
        $columns = $this->getColumns();
        $row = new Row(array(
            'type' => 'tr'
        ));
        $row->addCell(new Cell(array(
            'title' => '',
            'type' => 'th',
            'attr' => array(
                'checkbox' => true
            )
        )));
        $result = array_merge($columns, $result);
        foreach ($result as $key => $value) {
            if (isset($columns[$key])) {
                $attr = (isset($columns[$key]->getOptions() ['attr']) ? $columns[$key]->getOptions() ['attr'] : '');

                $pattern = '/(\w+)\_\_\_(\w+)\_\_(\w+)/';
                preg_match($pattern, $key, $match);
                $attr['data-role-lg-class'] = $match[2];
                $attr['data-role-lg-field'] = $columns[$key]->getValue();
                $title = (isset($columns[$key]->getOptions() ['title']) ? $columns[$key]->getOptions() ['title'] : $key);

                if (isset($columns[$key]->getOptions() ['hidden'])) {
                } else {

                    // putting a tilde in front of the title causes grid to interpret this as
                    // a result from another column in the query

                    if (false !== strpos($title, '~')) {
                        $title = $result[substr($title, 1) ];
                    }
                    $cell = new Cell(array(
                        'title' => $title,
                        'type' => 'th',
                        'attr' => $attr
                    ));
                    $row->addCell($cell);
                }
            } else {

                // no column!


            }
        }

        $thead->addRow($row);
    }

    public function fillTr(array $results = array())
    {
        $tbody = $this->getTable()->getTbody();
        $columns = $this->getColumns();
        foreach ($results as $row => $result) {
            $result = array_merge($columns, $result);
            $row = new Row(array(
                'type' => 'tr'
            ));
            $row->addCell(new Cell(array(
                'title' => '',
                'type' => 'td',
                'attr' => array(
                    'checkbox' => true
                )
            )));
            foreach ($result as $key => $value) {
                if (isset($columns[$key])) {
                    $attr = (isset($columns[$key]->getOptions() ['attr']) ? $columns[$key]->getOptions() ['attr'] : '');
                    if (isset($attr['entity_id']) && $attr['entity_id']) {
                        unset($attr['entity_id']);

                        $pattern = '/(\w+\_\_\_\w+)\_\_/';
                        preg_match($pattern, $key, $match);

                        $rootId = $match[1] . '__id';
                        $attr['data-role-lg-entity-id'] = $result[$rootId];
                    }

                    if (isset($columns[$key]->getOptions() ['hidden'])) {
                    } else {
                        $cell = new Cell(array(
                            'value' => $value,
                            'title' => $columns[$key]->getValue() ,
                            'type' => 'td',
                            'attr' => $attr,
                        ));
                        $row->addCell($cell);
                        $this->columnOptions($columns[$key]);
                    }
                } else {

                    // no column!


                }
            }

            $tbody->addRow($row);
        }
    }
}
