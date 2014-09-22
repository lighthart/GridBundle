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

    public function __toString()
    {
        return "Grid -- Don't print this -- print the table instead";
    }

    public function __construct(array $options = array())
    {
        $this->columns = array();
        $this->actions = array();
        $this->options = $options;
        $this->table = new Table(array(
            'attr' => $options['table']
        ) , !!(isset($options['html']) && $options['html']));
        $this->table->setGrid($this);
        $this->errors = array();
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
        if (array() != $result) {
            $result = $result[0];
        }

        // $row->addCell(new Cell(array(
        //     'title' => '',
        //     'type' => 'th',
        //     'attr' => array(
        //         'checkbox' => true
        //     )
        // )));

        $cells = array_merge($columns, $result);
        foreach ($cells as $key => $value) {
            if (isset($columns[$key])) {
                $attr = (isset($columns[$key]->getOptions() ['attr']) ? $columns[$key]->getOptions() ['attr'] : '');

                $pattern = '/(\w+)\_\_\_(\w+)\_\_(\w+)/';
                preg_match($pattern, $key, $match);

                $attr['data-role-lg-class'] = $match[2];
                $attr['data-role-lg-field'] = $columns[$key]->getValue();

                if (isset($columns[$key]->getOptions() ['search'])) {
                    $attr['class'].= ' lg-grid-searchable';
                }

                $title = ($columns[$key]->getOption('title') ? : $key);

                if (isset($columns[$key]->getOptions() ['hidden'])) {
                } else {

                    // tilde mapping
                    // putting a tilde in front of the title causes grid to interpret this as
                    // a result from another column in the query
                    // currently only handles one field

                    if (preg_match('/(.*?)~(((.*?)~)+)(.*?)/', $title, $match)) {
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
                    $html = ($columns[$key]->getOption('html') ? : null);
                    if ($html) {
                        $options['html'] = true;
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

                    $cell = new Cell(array(
                        'title' => $title,
                        'type' => 'th',
                        'attr' => $attr,
                        'options' => $options
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
                    $attr['class'].= ' lg-grid-filterable lg-grid-filter';
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

                //Not ready to implement this
                // $row->addCell(new Cell(array(
                //     'title' => '',
                //     'type' => 'td',
                //     'attr' => array(
                //         'checkbox' => true
                //     )
                // )));

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
                            $attr['class'].= ' lg-grid-searchable';
                        }
                        if (isset($columns[$key]->getOptions() ['filter'])) {
                            $attr['class'].= ' lg-grid-filterable';
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

        $results = $qb->getQuery()->getResult();
        if (array() != $results[0]) {
            foreach ($results[0] as $key => $value) {
                $attr = $visible[array_keys($visible) [$key - 1]]->getOptions() ['attr'];

                // Can't edit aggregates
                $attr['class'] = preg_replace('/\s*lg-editable\s*/', '', $attr['class']);

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
}
