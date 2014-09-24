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

    // what result we are on. 0-based
    private $offset;

    // how many for this query
    private $total;
    private $pageSize;
    private $search;
    private $errors;
    private $table;
    private $columns;
    private $options;
    private $actions;
    private $statuses;
    private $massAction;

    public function __toString()
    {
        return "Grid -- Don't print this -- print the table instead";
    }

    public function __construct(array $options = array())
    {
        $this->columns = array();
        $this->actions = array();
        $this->statuses = array();
        $this->options = $options;
        $this->table = new Table(array(
            'attr' => $options['table']
        ) , !!(isset($options['html']) && $options['html']));
        $this->table->setGrid($this);
        $this->errors = array();
        if (isset($options['massAction']) && $options['massAction']) {
            $this->massAction = true;
        }
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function setTotal($total)
    {
        $this->total = $total;
        return $this;
    }

    public function getPageSize()
    {
        return $this->pageSize ? : 10;
    }

    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
        return $this;
    }

    public function getSearch()
    {
        return $this->search;
    }

    public function setSearch($search)
    {
        $this->search = $search;
        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    public function addError($error)
    {
        $this->errors[] = $error;
        return $this;
    }

    public function hasErrors()
    {
        return array() != $this->errors;
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

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($option)
    {
        return isset($this->options[$option]) ? $this->options[$option] : null;
    }

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function getAction($action)
    {
        return isset($this->actions[$action]) ? $this->actions[$action] : null;
    }

    public function setActions(Array $actions)
    {
        $this->actions = $actions;
        return $this;
    }

    public function addAction($action)
    {
        $this->actions[] = $action;
        return $this;
    }

    public function getStatuses()
    {
        return $this->statuses;
    }

    public function getStatus($status)
    {
        return isset($this->statuses[$status]) ? $this->statuses[$status] : null;
    }

    public function setStatus($statuses)
    {
        $this->statuses = $statuses;
        return $this;
    }

    public function addStatus($status)
    {
        $this->statuses[] = $status;
        return $this;
    }

    // doesn't do much yet

    public function addMethod(Column $column)
    {
        $column->setOptions(array_merge($column->getOptions() , array(
            'method' => true
        )));
        $this->columns[$column->getAlias() ] = $column;
        return $this;
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

    public function fillErrors()
    {
        $thead = $this->getTable()->getThead();
        $columns = $this->getColumns();
        $row = new Row(array(
            'type' => 'tr'
        ));
        $row->addCell(new Cell(array(
            'title' => 'Grid Errors',
            'html' => true,
            'type' => 'th',
            'attr' => array(
                'class' => 'alert-danger alert'
            )
        )));
        $thead->addRow($row);
        foreach ($this->errors as $error) {
            $row = new Row(array(
                'type' => 'tr'
            ));
            $row->addCell(new Cell(array(
                'title' => $error,
                'type' => 'th',
                'attr' => array()
            )));
            $thead->addRow($row);
        }
    }

    public function fillTh(array $result = array() , $filters = true)
    {
        $thead = $this->getTable()->getThead();
        $columns = $this->getColumns();
        $row = new Row(array(
            'type' => 'tr'
        ));

        if ($this->massAction) {

            $row->addCell(new Cell(array(
                'title' => 'Mass',
                'type' => 'th',
                'attr' => array()
            )));
        }

        if (array() != $this->getActions()) {
            $actionCell = new Cell(array(
                'title' => 'Actions',
                'type' => 'th',
            ));
            $row->addCell($actionCell);
        }

        if (array() != $this->getStatuses()) {
            $statusCell = new Cell(array(
                'title' => 'Status',
                'type' => 'th',
            ));
            $row->addCell($statusCell);
        }

        if (array() != $result) {
            $result = $result[0];
        }

        $cells = array_merge($columns, $result);
        foreach ($cells as $key => $value) {
            if (isset($columns[$key])) {
                $attr = (isset($columns[$key]->getOptions() ['attr']) ? $columns[$key]->getOptions() ['attr'] : '');

                $pattern = '/(\w+)\_\_\_(\w+)\_\_(\w+)/';
                preg_match($pattern, $key, $match);

                $attr['data-role-lg-class'] = $match[2];
                $attr['data-role-lg-field'] = $columns[$key]->getValue();

                if (isset($columns[$key]->getOptions() ['search'])) {
                    $attr['class'].= ' lg-searchable';
                }

                $title = ($columns[$key]->getOption('title') ? : $key);

                if (isset($columns[$key]->getOptions() ['hidden'])) {
                } else {

                    // tilde mapping
                    // putting a tilde in front of the title causes grid to interpret this as
                    // a result from another column in the query
                    // currently only handles one field

                    if ('string' == gettype($title) && preg_match('/(.*?)~(((.*?)~)+)(.*?)/', $title, $match)) {
                        $matches = array_filter(explode('~', $match[2]));
                        if (array() == $result) {

                            $title = $match[1] . $match[5];
                        } else {

                            $title = $match[1] . implode(' ', array_map(function ($m) use (&$result)
                            {
                                if (isset($result[$m])) {
                                    return $result[$m];
                                } else {
                                    return $m;
                                }
                            }
                            , $matches)) . $match[5];
                        }
                    }

                    $parentId = ($columns[$key]->getOption('parentId') ? : null);

                    if ($parentId) {

                        // $attr['data-role-lg-parent-entity-id'] = $result[substr($parentId, 1) ];


                    }

                    $options = [];
                    $html = ($columns[$key]->getOption('titleHtml') ? : null);
                    if ($html) {
                        $options['titleHtml'] = true;
                    }

                    $entityId = ($columns[$key]->getOption('entityId') ? : null);
                    if ($entityId) {
                        $options['entityId'] = true;
                    }

                    if (preg_match('/.*?~(.+?)~.*?/', $parentId, $match)) {
                        $parentId = $match[1];

                        if (isset($result[$parentId])) {
                            $attr['data-role-lg-parent-entity-id'] = $result[$parentId];
                        } else {
                        }
                    }

                    $attr['title'] = $title;
                    $cell = new Cell(array(
                        'title' => $title,
                        'type' => 'th',
                        'attr' => $attr,
                        'options' => $options,
                    ));
                    $row->addCell($cell);
                }
            } else {

                // no column!


            }
        }
        $thead->addRow($row);
        $this->fillFilters($filters);
    }

    public function fillFilters($filters)
    {
        $thead = $this->getTable()->getThead();
        $columns = $this->getColumns();
        $row = new Row(array(
            'type' => 'tr',
        ));

        //Not ready to implement this
        if ($this->massAction) {
            $row->addCell(new Cell(array(
                'title' => '',
                'type' => 'th',
                'attr' => array(
                    'checkbox' => true
                )
            )));
        }

        if (array() != $this->getActions()) {
            $actionCell = new Cell(array(
                'title' => '',
                'type' => 'th',
            ));
            $row->addCell($actionCell);
        }

        if (array() != $this->getStatuses()) {
            $statusCell = new Cell(array(
                'title' => '',
                'type' => 'th',
            ));
            $row->addCell($statusCell);
        }

        foreach ($columns as $key => $column) {
            if (isset($columns[$key]->getOptions() ['hidden'])) {
            } else {
                $attr = (isset($columns[$key]->getOptions() ['attr']) ? $columns[$key]->getOptions() ['attr'] : '');
                if (isset($column->getOptions() ['filter'])) {

                    $pattern = '/(\w+)\_\_\_(\w+)\_\_(\w+)/';
                    preg_match($pattern, $key, $match);
                    $attr['data-role-lg-class'] = $match[1] . '___' . $match[2];
                    $attr['data-role-lg-field'] = $columns[$key]->getValue();
                    $attr['filter'] = $column->getOptions() ['filter'];
                    $attr['class'].= ' lg-filterable lg-filter';
                    $entityId = ($columns[$key]->getOption('entityId') ? : null);

                    if (!$filters) {
                        $attr['class'].= ' hide';
                    }
                    $cell = new Cell(array(
                        'title' => 'Filter' . $column->getOptions() ['filter'],
                        'type' => 'th',
                        'attr' => $attr
                    ));
                } else {
                    $attr['title'] = '';
                     // blank this out incase it was processed from column
                    $cell = new Cell(array(
                        'title' => '',
                        'type' => 'th',
                        'attr' => $attr
                    ));
                }
                $row->addCell($cell);
            }
        }
        $thead->addRow($row);
    }

    public function fillTr(array $results = array() , $root = null)
    {
        $tbody = $this->getTable()->getTbody();
        $columns = $this->getColumns();

        if (array() != $results) {

            foreach ($results as $tuple => $result) {
                $result = array_merge($columns, $result);
                if ($root) {
                    $row = new Row(array(
                        'type' => 'tr',
                        'attr' => array(
                            'data-role-lg-parent-entity-id' => $result[$root]
                        )
                    ));
                } else {
                    $row = new Row(array(
                        'type' => 'tr'
                    ));
                }

                if ($this->massAction) {
                    $row->addCell(new Cell(array(
                        'title' => '',
                        'type' => 'td',
                        'attr' => array(
                            'checkbox' => true
                        )
                    )));
                }

                if (array() != $this->getActions()) {
                    $actionCell = new ActionCell(array(
                        'title' => 'Actions',
                        'type' => 'td',
                        'actions' => $this->getActions() ,
                    ));
                    $row->addCell($actionCell);
                }
                if (array() != $this->getStatuses()) {
                    $statusCell = new StatusCell(array(
                        'title' => 'Status',
                        'type' => 'td',
                        'statuses' => $this->getStatuses() ,
                    ));
                    $row->addCell($statusCell);
                }
                foreach ($result as $key => $value) {

                    if (isset($columns[$key])) {
                        $attr = $columns[$key]->getOption('attr');
                        if ($columns[$key]->getOption('entityId')) {
                            $pattern = '/(\w+\_\_\_\w+)\_\_/';
                            preg_match($pattern, $key, $match);
                            $rootId = $match[1] . '__id';
                            $attr['data-role-lg-entity-id'] = $result[$rootId];
                        }

                        if (isset($columns[$key]->getOptions() ['search'])) {
                            $attr['class'].= ' lg-searchable';
                        }
                        if (isset($columns[$key]->getOptions() ['filter'])) {
                            $attr['class'].= ' lg-filterable';
                        }

                        $title = ($columns[$key]->getOption('title') ? : $key);

                        if (isset($columns[$key]->getOptions() ['hidden'])) {
                        } else {

                            // tilde mapping
                            // putting a tilde in front of the title causes grid to interpret this as
                            // a result from another column in the query
                            // currently only handles one field

                            $tildes = [&$title, &$value];
                            foreach ($tildes as $tildeKey => $what) {
                                if ('string' == gettype($what) && preg_match('/(.*?)~(((.*?)~)+)(.*?)/', $what, $match)) {
                                    $matches = array_filter(explode('~', $match[2]));
                                    if (array() == $result) {
                                        $what = $match[1] . $match[5];
                                    } else {
                                        $what = $match[1] . implode(' ', array_map(function ($m) use (&$result)
                                        {
                                            if (isset($result[$m])) {
                                                return $result[$m];
                                            } else {
                                                return $m;
                                            }
                                        }
                                        , $matches)) . $match[5];
                                    }
                                    $tildes[$tildeKey] = $what;
                                }
                            }

                            foreach ($attr as $attrKey => $what) {
                                if ('string' == gettype($what) && preg_match('/(.*?)~(((.*?)~)+)(.*?)/', $what, $match)) {
                                    $matches = array_filter(explode('~', $match[2]));
                                    if (array() == $result) {
                                        $what = $match[1] . $match[5];
                                    } else {
                                        $what = $match[1] . implode('', array_map(function ($m) use (&$result)
                                        {
                                            if (isset($result[$m])) {
                                                return $result[$m];
                                            } else {
                                                return $m;
                                            }
                                        }
                                        , $matches)) . $match[5];
                                    }
                                    $attr[$attrKey] = $what;
                                }
                            }

                            // if ('string' == gettype($value) && preg_match('/(.*?)~(((.*?)~)+)(.*?)/', $value, $match)) {
                            //     $matches = array_filter(explode('~', $match[2]));
                            //     if (array() == $result) {

                            //         $value = $match[1] . $match[5];
                            //     } else {

                            //         $value = $match[1] . implode(' ', array_map(function ($m) use (&$result)
                            //         {
                            //             if (isset($result[$m])) {
                            //                 return $result[$m];
                            //             } else {
                            //                 return $m;
                            //             }
                            //         }
                            //         , $matches)) . $match[5];
                            //     }
                            // }

                            // if (isset( $attr['title'] ) && 'string' == gettype($attr['title']) && preg_match('/(.*?)~(((.*?)~)+)(.*?)/', $attr['title'], $match)) {
                            //     $matches = array_filter(explode('~', $match[2]));
                            //     if (array() == $result) {

                            //         $attr['title'] = $match[1] . $match[5];
                            //     } else {

                            //         $attr['title'] = $match[1] . implode(' ', array_map(function ($m) use (&$result)
                            //         {
                            //             if (isset($result[$m])) {
                            //                 return $result[$m];
                            //             } else {
                            //                 return $m;
                            //             }
                            //         }
                            //         , $matches)) . $match[5];
                            //     }
                            // }
                            $cell = new Cell(array(
                                'value' => $value,
                                'title' => $title,
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
        } else {
            $row = new Row(array(
                'type' => 'tr'
            ));
            $attr = [];
            $attr['colspan'] = count(array_filter($columns, function ($c)
            {
                return !isset($c->getOptions() ['hidden']);
            }));
            $cell = new Cell(array(
                'value' => 'No Results',
                'title' => 'No Results',
                'type' => 'td',
                'attr' => $attr,
            ));
            $row->addCell($cell);
            $tbody->addRow($row);
        }
    }

    public function fillAggregate($qb)
    {

        // this is handled positionally, not by reference.
        // that might have to change later
        $tbody = $this->getTable()->getTbody();
        $columns = $this->getColumns();
        $visible = array_filter($columns, function ($c)
        {
            return !$c->getOption('hidden');
        });

        $row = new Row(array(
            'type' => 'tr'
        ));

        //Not ready to implement this
        if ($this->massAction) {
            $row->addCell(new Cell(array(
                'title' => '',
                'type' => 'td',
                'attr' => array()
            )));
        }
        if (array() != $this->getActions()) {
            $actionCell = new Cell(array(
                'title' => 'Actions',
                'type' => 'td',
            ));
            $row->addCell($actionCell);
        }

        if (array() != $this->getStatuses()) {
            $statusCell = new Cell(array(
                'title' => 'Status',
                'type' => 'td',
            ));
            $row->addCell($statusCell);
        }

        $aqb = clone $qb;
        $aqb->setFirstResult(0);

        $results = $aqb->getQuery()->getResult();
        if (array()!= $results && array() != $results[0]) {
            foreach ($results[0] as $key => $value) {
                $attr = $visible[array_keys($visible) [$key - 1]]->getOptions() ['attr'];

                // Can't edit aggregates
                $attr['class'] = preg_replace('/\s*lg-editable\s*/', '', $attr['class']);
                $attr['emphasis'] = 'strong';

                $cell = new Cell(array(
                    'value' => $value,
                    'title' => "Summary for " . $visible[array_keys($visible) [$key - 1]]->getValue() ,
                    'type' => 'td',
                    'attr' => $attr,
                ));
                $row->addCell($cell);
            }
            $tbody->addRow($row);
        }
    }

    public function getMassAction()
    {
        return $this->massAction;
    }

    public function setMassAction($massAction)
    {
        $this->massAction = $massAction;
        return $this;
    }
}
