<?php
namespace Lighthart\GridBundle\Grid;

// use Knp\Component\Pager\Paginator;

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
    private $router;
    private $aliases;
    private $export;

    /**
     * This should never be used -- method is so there is not an exception thrown.
     *
     * @return string
     */
    public function __toString()
    {
        return "Grid -- Don't print this -- print the table instead";
    }

    public function __construct(array $options = [])
    {
        $this->columns  = [];
        $this->actions  = [];
        $this->statuses = [];
        $this->options  = $options;
        $this->table    = new Table(['attr' => $options['table']], !!(isset($options['html']) && $options['html']));
        $this->table->setGrid($this);
        $this->errors = [];
        if (isset($options['massAction']) && $options['massAction']) {
            $this->massAction = true;
        }
        if (isset($options['export']) && $options['export']) {
            $this->massAction = true;
        }
    }

    public function getRouter()
    {
        return $this->router;
    }

    public function setRouter($router)
    {
        $this->router = $router;

        return $this;
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
        return $this->pageSize ?: 100;
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
        return [] != $this->errors;
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

    public function getAliases()
    {
        return $this->aliases;
    }

    public function setAliases($aliases)
    {
        $this->aliases = $aliases;

        return $this;
    }

    public function getExport()
    {
        return $this->export;
    }

    public function setExport($export = 1000)
    {
        $this->export = $export;

        return $this;
    }

    public function addColumn(Column $column)
    {
        $this->columns[$column->getAlias()] = $column;

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

    public function getColumn($column)
    {
        return $this->columns[$column];
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
        $tr    = new Tr();
        array_map(function ($col) use (&$tr) {
            $th = new Th(['title' => $col]);
            $tr->addTh($th);
        }, $this->getColumns()->getOrder());
        $thead->addTr($tr);
    }

    public function columnOptions(Column $column) {}
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

    public function setOption(
        $option,
        $value
    ) {
        $this->options[$option] = $value;

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

    public function setActions(array $actions)
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
        $column->setOptions(array_merge($column->getOptions(), ['method' => true]));
        $this->columns[$column->getAlias()] = $column;

        return $this;
    }

    public function verifyClass(
        String $class,
               $slash = null
    ) {
        // Default is class name is sent with backslashes
        // if another delimiter is used, for example '/' or '_'
        // Send as parameter

        if ($slash) {
            $backslash = str_replace($slash, '\\', $class);
        }
        $metadataFactory = $em->getMetadataFactory();

        $error = '';

        if (!$class) {
            $error .= 'Class for grid verify not specified';
        }

        try {
            $metadata = $metadataFactory->getMetadataFor($backslash);
        } catch (\Exception $ex) {
            $metadata = null;
            $error .= 'No metadata for class: ' . $backslash;
        }

        if ('' != $error) {
            $error = 'grid.maker error: ' . $error;
        }

        if ($metadata) {
            return ['class' => $class, 'metadata' => $metadata, 'error' => null];
        } else {
            ['class' => null, 'metadata' => null, 'error' => $error];
        }
    }

    public function fillErrors()
    {
        $thead   = $this->getTable()->getThead();
        $columns = $this->getColumns();
        $row     = new Row(['type' => 'tr']);
        $row->addCell(new Cell(['type' => 'th']));
        $row->addCell(new Cell(['title' => 'Grid Errors', 'value' => 'Grid Errors', 'html' => true, 'type' => 'th', 'attr' => ['class' => 'alert-danger alert', 'colspan' => 100]]));
        $thead->addRow($row);
        foreach ($this->errors as $error) {
            $row = new Row(['type' => 'tr']);
            $row->addCell(new Cell(['type' => 'th']));
            $row->addCell(new Cell(['title' => $error, 'value' => $error, 'type' => 'th', 'attr' => ['class' => 'alert-danger alert', 'colspan' => 100]]));
            $thead->addRow($row);
        }
    }

    public function fillTh(
        array $result = [],
              $filters = true,
              $filterValues = []
    ) {
        $thead   = $this->getTable()->getThead();
        $columns = $this->getColumns();
        $row     = new Row(['type' => 'tr', 'attr' => ['class' => 'lg-headers']]);

        //Not ready to implement this
        if ($this->massAction && !$this->export) {
            $row->addCell(new Cell(['title' => '', 'type' => 'th', 'attr' => ['checkbox' => true, 'class' => '', 'width' => '1em']]));
        }

        if (([] != $this->getActions()) && !$this->export) {
            $actionCell = new Cell(['title' => 'Actions', 'type' => 'th']);
            $row->addCell($actionCell);
        }

        //Not ready to implement this
        if (([] != $this->getStatuses()) && !$this->export) {
            $statusCell = new Cell(['title' => 'Status', 'type' => 'th', 'attr' => ['class' => 'lg-filterable lg-filter']]);
            $row->addCell($statusCell);
        }

        if ([] != $result) {
            $result = $result[0];
        }

        $cells = array_merge($columns, $result);
        foreach ($cells as $key => $value) {
            if (isset($columns[$key])) {
                $attr = (isset($columns[$key]->getOptions()['attr']) ? $columns[$key]->getOptions()['attr'] : '');

                $pattern = '/(\w+)\_\_\_(\w+)\_\_(\w+)/';
                preg_match($pattern, $key, $match);
                if ($match) {
                    $attr['data-role-lg-class'] = $match[1] . '___' . $match[2];
                    $attr['data-role-lg-field'] = $columns[$key]->getValue();
                }

                $attr['class'] = (isset($attr['class']) ? $attr['class'] : "");
                if (isset($columns[$key]->getOptions()['search'])) {
                    $attr['class'] .= ' lg-searchable';
                }

                $title  = ($columns[$key]->getOption('title') ?: $key);
                $header = ($columns[$key]->getOption('header') ?: $title);

                if (isset($columns[$key]->getOptions()['hidden']) && $columns[$key]->getOptions()['hidden']) {
                } else {
                    // tilde mapping
                    // putting a tilde in front of the title causes grid to interpret this as
                    // a result from another column in the query
                    // currently only handles one field

                    $this->tildes([ & $title], $result);
                    $this->tildes([ & $header], $result);
                    $parentId = ($columns[$key]->getOption('parentId') ?: null);

                    if ($parentId) {
                        // $attr['data-role-lg-parent-entity-id'] = $result[substr($parentId, 1) ];
                    }

                    $options = [];
                    $html    = ($columns[$key]->getOption('titleHtml') ?: null);
                    if ($html) {
                        $options['titleHtml'] = true;
                    }

                    $sort = ($columns[$key]->getOption('sort') ?: null);
                    if ($sort) {
                        $options['sort'] = true;
                    }

                    $boolean = ($columns[$key]->getOption('boolean') ?: null);
                    if ($boolean) {
                        $options['boolean'] = true;
                    }

                    $entityId = ($columns[$key]->getOption('entityId') ?: null);
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

                    $attr['title']  = $title;
                    $attr['header'] = $header;
                    $cell           = new Cell(['title' => $title, 'header' => $header, 'type' => 'th', 'attr' => $attr, 'options' => $options]);
                    $row->addCell($cell);
                }
            } else {
                // no column!
            }
        }

        $thead->addRow($row);
        if (([] == array_filter($columns, function ($c) {
            return $c->getOption('filter');
        })) || $this->export) {
        } else {
            $this->fillFilters($filters, $filterValues);
        }
    }

    public function fillFilters(
        $filters,
        $filterValues = []
    ) {
        $thead   = $this->getTable()->getThead();
        $columns = $this->getColumns();
        $row     = new Row(['type' => 'tr', 'attr' => ['class' => 'lg-filters' . ($filters ? '' : ' hide')]]);

        //Not ready to implement this
        if ($this->massAction) {
            $row->addCell(new Cell(
                [
                    'title' => ' ',
                    'type'  => 'th',
                    'attr'  => [
                        'class' => 'lg-filterable lg-filter' . ($filters ? '' : ' hide'),
                    ],
                ])
            );
        }

        if ([] != $this->getActions()) {
            $actionCell = new Cell(
                [
                    'title' => ' ',
                    'type'  => 'th',
                    'attr'  => [
                        'class' => 'lg-filterable lg-filter' . ($filters ? '' : ' hide'),
                    ],
                ]);

            $row->addCell($actionCell);
        }

        if ([] != $this->getStatuses()) {
            $statusCell = new Cell(
                [
                    'title' => '',
                    'type'  => 'th',
                    'attr'  => [
                        'class' => 'lg-filterable lg-filter',
                    ],
                ]);
            $row->addCell($statusCell);
        }

        foreach ($columns as $key => $column) {
            if (!isset($column->getOptions()['filter'])) {
                $columnOptions           = $column->getOptions();
                $columnOptions['filter'] = false;
                $column->setOptions($columnOptions);
            }
        }

        foreach ($filterValues as $filterValue) {
            $fv  = substr(strstr($filterValue, ':'), 1);
            $col = strstr($filterValue, ':', true);

            $filterFields[$col] = $fv;
        }

        foreach ($columns as $key => $column) {
            if (isset($columns[$key]->getOptions()['hidden'])) {
            } else {
                $attr = (isset($columns[$key]->getOptions()['attr']) ? $columns[$key]->getOptions()['attr'] : '');
                if (isset($column->getOptions()['filter'])) {
                    $pattern = '/(\w+)\_\_\_(\w+)\_\_(\w+)/';
                    preg_match($pattern, $key, $match);
                    if ($match) {
                        $attr['data-role-lg-class'] = $match[1] . '___' . $match[2];
                        $attr['data-role-lg-field'] = $columns[$key]->getValue();
                        if ($columns[$key]->getOption('filterHidden')) {
                            $aliases                     = $this->getAliases();
                            $attr['data-role-lg-hidden'] = implode(';', array_map(function ($o) use ($aliases) {
                                if (isset($aliases[$o])) {
                                    return $aliases[$o];
                                } else {
                                    return $o;
                                }
                            },
                                'array' == gettype($columns[$key]->getOption('filterHidden')) ?
                                $columns[$key]->getOption('filterHidden') :
                                explode(';', $columns[$key]->getOption('filterHidden'))
                            )
                            );
                        }
                    }

                    // This is some pretty cantekerous bullshit.
                    //
                    // Need to go to an array merging approach/combining tuples

                    if ($columns[$key]->getOption('otherGroup') && $columns[$key]->getOption('filterHidden')) {
                        $attr['data-role-lg-class'] = stristr($columns[$key]->getAlias(), '__otherGroup', true);
                        // $attr['data-role-lg-field'] = 'otherGroup';
                        $aliases                     = $this->getAliases();
                        $attr['data-role-lg-hidden'] =
                            implode(';',
                            array_map(function ($o) use ($aliases) {
                                if (isset($aliases[$o])) {
                                    return $aliases[$o];
                                } else {
                                    return $o;
                                }
                            },
                                array_filter(
                                    $columns[$key]->getOption('filterHidden'),
                                    function ($e) use ($aliases) {
                                        return isset($aliases[$e]);
                                    }
                                )
                            )
                        );
                    }

                    $attr['filter'] = $column->getOptions()['filter'];
                    $attr['class']  = (isset($attr['class']) ? $attr['class'] : "");
                    $attr['class'] .= ' lg-filterable lg-filter';
                    $entityId = ($columns[$key]->getOption('entityId') ?: null);
                    if (!$filters) {
                        $attr['class'] .= ' hide';
                    } else {
                        if (isset($filterFields[$key])) {
                            $attr = array_merge($attr, ['filterValue' => $filterFields[$key]]);
                        }

                    }
                    $cell = new Cell(['type' => 'th', 'attr' => $attr]);
                } else {
                    $attr['title'] = '';

                    // blank this out incase it was processed from column
                    $cell = new Cell(['title' => '', 'type' => 'th', 'attr' => $attr]);
                }
                $row->addCell($cell);
            }
        }
        $thead->addRow($row);
    }

    public function exportTh()
    {
        $columns = $this->getColumns();
        $columns = array_filter($this->getColumns(), function ($col) {
            return (!array_key_exists('hidden', $col->getOptions()) || !$col->getOption('hidden'));
        });

        return array_map(function ($col) {
            return $col->getOption('title');
        }, $columns);
    }

    public function exportTr(
        array $results = [],
              $root = null
    ) {
        $newResults = [];
        $headers    = $this->exportTh();
        $columns    = $this->getColumns();
        $booleans   = array_keys(array_filter($columns, function ($col) {
            return $col->getOption('boolean');
        }));

        foreach ($this->aliases as $key => $value) {
            $exportAliases[$key] = str_replace('.', '_', $key);
        }

        //
        if ([] != $results) {
            foreach ($results as $tuple => $result) {
                foreach ($headers as $headerKey => $headerValue) {
                    if ($this->getColumn($headerKey)->getOption('value')) {
                        if ($columns[$headerKey]->getOption('value')) {
                            if ('array' == gettype($columns[$headerKey]->getOption('value'))) {
                                $values = $columns[$headerKey]->getOption('value');
                            } else {
                                $values = [$columns[$headerKey]->getOption('value')];
                            }
                            // not getting all of the tildes here... need to fix
                            $values = array_map(function ($v) use ($result, $headerKey) {
                                if (strpos($v, '~') !== false) {
                                    $key = str_replace('~', '', str_replace('.', '_', $v));

                                    if (isset($result[$key])) {
                                        return $result[$key];
                                    } else {
                                        return $result[$headerKey];
                                    }
                                } else {
                                    return $v;
                                }
                            }, $values);

                            // array filter strips out non-truthy values
                            $values = array_filter($values);

                            $values = array_filter(array_map(function ($v) use ($result) {
                                return $this->tilde($v, $result);
                            }, $values));
                            $value = array_shift($values);

                        }

                        $newResult[$headerKey] = $value;
                    } elseif (in_array($headerKey, $booleans)) {
                        $boolean = $this->getColumn($headerKey)->getOptions('boolean');
                        if ('object' == gettype($boolean) && 'Closure' == get_class($boolean)) {
                            $tmpResult             = $boolean($result, $exportAliases);
                            $newResult[$headerKey] = ((null === $tmpResult) ? "null" : ($tmpResult ? "true" : "false"));
                        } else {
                            $newResult[$headerKey] = ((null === $result[$headerKey]) ? "null" : ($result[$headerKey] ? "true" : "false"));
                        }
                    } else {
                        $newResult[$headerKey] = (($result[$headerKey] instanceof \DateTime) ? $result[$headerKey]->format('Y-m-d') : $result[$headerKey]);
                    }
                }

                $newResults[] = $newResult;
            }
        }

        return $newResults;
    }

    public function fillTr(
        array $results = [],
              $root = null
    ) {
        $tbody   = $this->getTable()->getTbody();
        $columns = $this->getColumns();
        if ([] != $results) {
            foreach ($results as $tuple => $result) {
                $result = array_merge($columns, $result);

                if ($root) {
                    $row = new Row(['type' => 'tr', 'attr' => ['data-role-lg-parent-entity-id' => $result[$root]]]);
                } else {
                    $row = new Row(['type' => 'tr']);
                }

                if ($this->massAction && !$this->export) {
                    $row->addCell(new Cell(['title' => '', 'type' => 'td', 'attr' => ['checkbox' => true]]));
                }

                $cellActions = [];
                if (([] != $this->getActions()) && !$this->export) {
                    foreach ($this->getActions() as $slug => $action) {
                        // figure out a bail out clause
                        // which uses tildes
                        $newAction = clone $action;
                        $security  = $action->getSecurity();
                        if (is_bool($security)) {
                            // default is true, set in the Action constructor
                        } else {
                            $security = $security($result, $this->aliases);
                        }

                        if ($security) {
                            $actionTitle = $action->getTitle();
                            $this->tildes([ & $actionTitle], $result);
                            $newAction->setTitle($actionTitle);
                            if ($newAction->getRoute()) {
                                $routeConfig = $newAction->getRoute();
                                if ('array' == gettype($routeConfig)) {
                                    foreach ($routeConfig as $routeKey => $params) {
                                        if ('array' == gettype($params)) {
                                            foreach ($params as $paramKey => $param) {
                                                $routeConfig[$routeKey][$paramKey] = $this->tilde($param, $result);
                                            }
                                            try {
                                                $newAction->setRoute($this->router->generate($routeKey, $routeConfig[$routeKey]));
                                            } catch (\Exception $e) {
                                                $newAction = null;
                                            }
                                        }
                                    }
                                }
                            } else {
                                $newAction = null;
                            }

                            if ($newAction) {
                                $cellActions[] = $newAction;
                            }
                        }
                    }

                    $actionCell = new ActionCell(['title' => 'Actions', 'type' => 'td', 'actions' => $cellActions]);
                    $row->addCell($actionCell);
                }

                if (([] != $this->getStatuses()) && !$this->export) {
                    $statusCell = new StatusCell(['title' => 'Status', 'type' => 'td', 'statuses' => $this->getStatuses()]);
                    $row->addCell($statusCell);
                }

                foreach ($result as $key => $value) {
                    if (isset($columns[$key])) {
                        $title = ($columns[$key]->getOption('title') ?: $key);
                        $attr  = $columns[$key]->getOption('attr');
                        if ($columns[$key]->getOption('entityId')) {
                            $pattern = '/(\w+\_\_\_\w+)\_\_/';
                            preg_match($pattern, $key, $match);
                            if ($match) {
                                $rootId                         = $match[1] . "__id";
                                $attr['data-role-lg-entity-id'] = $result[$rootId];
                                $attr['data-role-th']           = $title;
                            }
                        }

                        $attr['class'] = (isset($attr['class']) ? $attr['class'] : "");
                        if (isset($columns[$key]->getOptions()['search'])) {
                            $attr['class'] .= ' lg-searchable';
                        }
                        if (isset($columns[$key]->getOptions()['filter'])) {
                            $attr['class'] .= ' lg-filterable';
                        }

                        $security = $columns[$key]->getSecurity();
                        if (is_bool($security)) {
                            // default is true, set in the Action constructor
                        } else {
                            $security = $security($result, $this->aliases);
                        }
                        if ($columns[$key]->getOption('hidden')) {
                        } else {
                            // tilde mapping
                            // putting a tilde in front of the title causes grid to interpret this as
                            // a result from another column in the query
                            // currently only handles one field
                            $tildeAttr = [];
                            $this->tildes([ & $title, &$value], $result);
                            foreach ($attr as $k => $attrib) {
                                if (false !== strpos($attrib, '~')) {
                                    $tildeAttr[$k] = $attrib;
                                }
                            }
                            foreach ($tildeAttr as $tildeKey => $attrib) {
                                $tildeAttr[$tildeKey] = $this->tilde($attrib, $result);
                            }

                            $attr = array_merge($attr, $tildeAttr);

                            if ($columns[$key]->getOption('value')) {
                                if ('array' == gettype($columns[$key]->getOption('value'))) {
                                    $values = $columns[$key]->getOption('value');
                                } else {
                                    $values = [$columns[$key]->getOption('value')];
                                }

                                $values = array_filter(array_map(function ($v) use ($result) {
                                    return $this->tilde($v, $result);
                                }, $values));
                                $value = array_shift($values);

                                // if ('array' == gettype($columns[$key]->getOption('value'))) {
                                // }
                            }

                            $transform = $columns[$key]->getTransform();
                            if ($transform) {
                            }

                            if ($transform) {
                                $value = $transform($result, $this->aliases);
                            }

                            if ($title != $key) {
                                $attr['title'] = $title;
                            }

                            if (array_key_exists('title', $attr) && $attr['title']) {
                            } else {
                                if (!is_object($value)) {
                                    $attr['title'] = $value;
                                }
                            }

                            $options = [];
                            $boolean = ($columns[$key]->getOption('boolean') ?: null);
                            if ($boolean) {
                                $options['boolean'] = true;
                                if ('string' == gettype($boolean)) {
                                    $value = ($value == $boolean);
                                } elseif ('object' == gettype($boolean) && 'Closure' == get_class($boolean)) {
                                    $value = $boolean($result, $this->aliases);
                                }
                            }

                            $money = ($columns[$key]->getOption('money') ?: null);
                            if ($money) {
                                $options['money'] = true;
                            }

                            $edit = ($columns[$key]->getOption('edit') ?: null);
                            if ($edit) {
                                $options['edit'] = true;
                            }

                            $group = ($columns[$key]->getOption('group') ?: null);
                            if ($group) {
                                $options['group'] = true;
                            }

                            if ($columns[$key]->getOption('trunc')) {
                                ;
                                $value = substr($value, 0, $columns[$key]->getOption('trunc'));
                            }

                            if (!$security) {
                                $value = null;
                            }

                            $cell = new Cell(['value' => $value, 'title' => $title, 'type' => 'td', 'attr' => $attr, 'options' => $options]);
                            $row->addCell($cell);

                            $this->columnOptions($columns[$key]);
                        }
                    } else {
                        // no column!
                    }
                }
                $tbody->addRow($row);

                // stops after one row.  convenient for debugging
                // die;
            }
        } else {
            $row             = new Row(['type' => 'tr']);
            $attr            = [];
            $attr['colspan'] = count(array_filter($columns, function ($c) {
                return !isset($c->getOptions()['hidden']);
            }));
            $attr['noResults'] = true;
            $cell              = new Cell(['value' => 'No Results', 'title' => 'No Results', 'type' => 'td', 'attr' => $attr]);
            $row->addCell($cell);
            $tbody->addRow($row);
        }

        // stops after all rows.  Also useful for debuggins
        // var_dump($results);
        // die;
    }

    public function fillAggregate($qb)
    {
        // this is handled positionally, not by reference.
        // that might have to change later
        $tbody   = $this->getTable()->getTbody();
        $columns = $this->getColumns();
        $visible = array_filter($columns, function ($c) {
            return !$c->getOption('hidden');
        });

        $row = new Row(['type' => 'tr', 'attr' => ['class' => 'lg-aggregate-row']]);

        //Not ready to implement this
        if ($this->massAction && !$this->export) {
            $row->addCell(new Cell(['title' => '', 'type' => 'td', 'attr' => []]));
        }
        if (([] != $this->getActions()) && !$this->export) {
            $actionCell = new Cell(['title' => 'Actions', 'type' => 'td']);
            $row->addCell($actionCell);
        }

        if (([] != $this->getStatuses()) && !$this->export) {
            $statusCell = new Cell(['title' => 'Status', 'type' => 'td']);
            $row->addCell($statusCell);
        }

        $aqb = clone $qb;
        $aqb->setFirstResult(0);

        $results = $aqb->getQuery()->getResult();
        if ([] != $results && [] != $results[0]) {
            foreach ($results[0] as $key => $value) {
                $options              = $visible[array_keys($visible)[$key - 1]]->getOptions();
                $options['aggregate'] = true;
                $attr                 = $options['attr'];

                // Can't edit aggregates
                $attr['class'] = preg_replace('/\s*lg-editable\s*/', '', $attr['class']);
                $attr['class'] .= " lg-aggregate";
                $attr['emphasis'] = 'strong';

                if (isset($attr['data-role-lg-editable'])) {
                    unset($attr['data-role-lg-editable']);
                }

                if (isset($attr['data-role-lg-new'])) {
                    unset($attr['data-role-lg-new']);
                }

                $cell = new Cell(['value' => $value, 'title' => "Summary for " . $visible[array_keys($visible)[$key - 1]]->getValue(), 'type' => 'td', 'attr' => $attr,

                    // this next one might need some granularity
                    'options'                 => $options]);
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

    public function tilde(
         $what,
        &$result
    ) {
        // converts ~column.def~ into the value from the result
        if ('string' == gettype($what)) {
            while (preg_match('/^(.*?)(~.*?~)(.*?)$/', $what, $match)) {
                $matches = array_filter(explode('~', $match[2]));
                if ([] == $result) {
                    $what = $match[1] . $match[3];
                } else {
                    $what = $match[1] . implode('', array_map(function ($m) use (&$result) {
                        if (false !== strpos($m, '|')) {
                            // grab the first truthy value
                            $m = array_shift(array_filter(explode('|', $m), function ($v) use ($result) {
                                return $result[$v];
                            }));
                        }
                        if (array_key_exists($m, $result)) {
                            return $result[$m];
                        } else {
                            // mark them different so we don't recurse forever
                            if (false === strpos($m, '___')) {
                                return '%' . $m . '%';
                            } else {
                                // in this case no match was found-- remove the tilde tag
                                return '';
                            }
                        }
                    }, $matches)) . $match[3];
                }
            }
            $what = str_replace('%', '~', $what);
        }

        // change marks back
        return $what;
    }

    public function tildes(
        array $tildes,
        &     $result
    ) {
        foreach ($tildes as $tildeKey => $what) {
            $tildes[$tildeKey] = $this->tilde($what, $result);
        }

        return $tildes;
    }
}
