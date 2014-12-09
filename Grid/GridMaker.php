<?php
namespace Lighthart\GridBundle\Grid;

// use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;

class GridMaker
{

    private $doctrine;
    private $router;
    private $request;
    private $dql;
    private $query;
    private $queryBuilder;
    private $grid;

    public function __toString()
    {
        return "Grid Maker -- Don't print this";
    }

    public function __construct($doctrine, $router)
    {
        $this->doctrine = $doctrine;
        $this->router = $router;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function getGrid()
    {
        return $this->grid;
    }

    public function setGrid(Grid $grid)
    {
        $this->grid = $grid;
        return $this;
    }

    public function newGrid()
    {
        $this->grid = new Grid();
        return $this;
    }

    public function getDQL()
    {
        if ($this->dql) {
            return $this->dql;
        } else {
            return $this->queryBuilder->getQuery()->getDQL();
        }
    }

    public function setDQL($dql)
    {
        $this->dql = $dql;
        return $this;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    public function Q()
    {
        return $this->getQuery();
    }

    public function getQ()
    {
        return $this->getQuery();
    }

    public function setQ($query)
    {
        return $this->setQuery($query);
    }

    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    public function setQueryBuilder($queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        return $this;
    }

    public function QB()
    {
        return $this->getQueryBuilder();
    }

    public function getQB()
    {
        return $this->getQueryBuilder();
    }

    public function setQB($queryBuilder)
    {
        return $this->setQueryBuilder($queryBuilder);
    }

    public function initialize($options = array())
    {
        $this->grid = new Grid($options);
        $this->grid->setRouter($this->router);
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
            return array('class' => $class, 'metadata' => $metadata, 'error' => null,);
        } else {
            array('class' => null, 'metadata' => null, 'error' => $error,);
        }
    }

    public function addField($entity, $value = 'id', array $options = array())
    {
        $this->getGrid()->addColumn(new Column($entity . '_' . $value, $value, $options));
    }

    public function addMethod($entity, $method, array $options = array())
    {
        if (method_exists($entity, $method)) {
            $this->getGrid()->addMethod(new Column($entity, $method, $options));
        }
    }

    public function addAction($options)
    {
        $this->getGrid()->addAction(new Action($options));
    }

    public function addStatus($options)
    {
        $this->getGrid()->addStatus(new Status($options));
    }

    public function hydrateGrid(Request $request, $options = array() )
    {
        set_time_limit(0);
        $defaultOptions = array('fromQB' => false, 'export' => false);
        $options = array_merge($defaultOptions, $options );
        $fromQB = $options['fromQB'];
        $export = $options['export'];
        if ($export) { $this->setExport(); }
        $debug = $request->query->get('debug');
        $filters = !!$request->cookies->get('lg-filter-toggle');

        // this is for autogeneration of grid from QB instead of column specifications
        // it is not really built as of Sep 2014
        if ($fromQB) {
            $this->mapFieldsFromQB();
        } else {
            $this->mapFieldsFromColumns();
        }

        $this->mapMethodsFromQB();

        var_dump(__LINE__);
        $cookies = $request->cookies;
        $pageSize = $request->cookies->get('lg-results-per-page') ? : 10;
        $pageOffset = $request->cookies->get("lg-" . $request->attributes->get('_route') . "-offset");
        $search = $request->cookies->get("lg-" . $request->attributes->get('_route') . "-search");
        $filter = $request->cookies->get("lg-" . $request->attributes->get('_route') . "-filter");
        $sort = $request->cookies->get("lg-" . $request->attributes->get('_route') . "-sort");
        $this->addFilter($filter);
        $this->addSearch($search);
        $cqb = clone $this->QB();
        $root = $cqb->getDQLPart('from') [0]->getAlias() . ".id";
        $cqb->resetDQLPart('orderBy');
        $cqb->select($cqb->expr()->count($root));
        $cqb->distinct();
        $this->getGrid()->setTotal($cqb->getQuery()->getSingleScalarResult());

        $offset = ($request->query->get('pageOffset') ? : ($pageOffset ? : 0));
        $offset = ($offset > $this->getGrid()->getTotal()) ? $offset = $this->getGrid()->getTotal() - $pageSize : $offset;
        $offset = ($offset < 0) ? 0 : $offset;
        $offset = floor($offset / $pageSize) * $pageSize;


        $this->getGrid()->setSearch($search);

        if ($this->getGrid()->getOption('singlePage')) {
        }


        $orderBys = $this->QB()->getDQLPart('orderBy');
        $this->QB()->resetDQLPart('orderBy');

        $sorts = explode(';', $sort);
        foreach ($sorts as $key => $srt) {
            if (preg_match('/(.*?)\_\_\_(.*?)\_\_(.*?)\:(.*)/', $srt, $match)) {
                if ($match[4]) {
                    $this->QB()->add('orderBy', $match[1] . '.' . $match[3] . ' ' . $match[4], true);
                }
            }
        }

        foreach ($orderBys as $k => $part) {
            $this->QB()->add('orderBy', $part, true);
        }




        if (!$export) {
            $this->getGrid()->setPageSize($pageSize);
            $this->getGrid()->setOffset($offset);
            $this->QB()->setFirstResult($offset);
            $this->QB()->setMaxResults($pageSize);
        } else {
            // this needs to be changed to something for iteration

            $this->getGrid()->setPageSize($pageSize);
            $this->getGrid()->setOffset($offset);
            $this->QB()->setFirstResult($offset);
            $this->QB()->setMaxResults($pageSize);
        }

        $q = $this->QB()->getQuery();
        $q->setDql($this->mapAliases());

        var_dump($pageSize);

        var_dump(__LINE__);
        print_r($q->getSQL());
        $results = $q->getResult(Query::HYDRATE_SCALAR);
        var_dump($results);die;
        if (array() == $results) {
            $root = 'root';
        } else {
            $root = preg_grep('/root\_\_\_(.*?)\_\_id/', array_keys($results[0]));
            $root = $root[array_keys($root) [0]];
        }

        $html = $this->getGrid()->getOption('html');
        if ($html) {
            if ($this->getGrid()->getOption('aggregateOnly')) {

                // aggregate only

            } else {
                $this->getGrid()->fillTh($results, $filters);
                $this->getGrid()->fillTr($results, $root);
            }
            if ($this->getGrid()->hasErrors()) {
                $this->getGrid()->fillErrors($results, $filters);
            }

            $sums = array_filter($this->getGrid()->getColumns(), function ($c)
            {
                return in_array('aggregate', array_keys($c->getOptions()));
            });

            if (array() !== $results && array() != $sums ) {
                $this->getGrid()->fillAggregate($this->aggregateQuery());
            } else {
            }
        }
    }

    public function pregAlias($alias, $aliases)
    {
        if (preg_match('/(.*?)~(((.*?)~)+)(.*)/', $alias, $match)) {

            $matches = array_filter(explode('~', $match[2]));
            foreach ($matches as $key => $col) {
                if (preg_match('/\<(.*?)\>/', $col)) {
                } else {
                    $oldField = substr(stristr($col, '.'), 1);
                    $oldSubAlias = stristr($col, '.', true);
                    if (false !== $oldSubAlias) {
                        $matches[$key] = $aliases[$oldSubAlias] . '_' . $oldField;
                    }
                }
            }
            $alias = $match[1] . '~' . implode('~', $matches) . '~' . $match[5];
        }

        return $alias;
    }

    public function mapAliases()
    {
        $qb = $this->queryBuilder;
        $dql = $qb->getQuery()->getDQL();

        $aliases = [];
        $from = $qb->getDqlPart('from') [0];
        $rootClassPath = $from->getFrom();
        $oldRoot = $qb->getRootAlias();

        // mark root
        $root = 'root___' . str_replace('\\', '_', $rootClassPath . '_');
        $aliases[$oldRoot] = $root;
        $entities[$oldRoot] = $rootClassPath;

        $em = $qb->getEntityManager();
        $em->getMetadataFactory()->getAllMetadata();

        // realiased qb

        $rqb = $em->getRepository($rootClassPath)->createQueryBuilder($root);

        $joins = $qb->getDqlPart('join') [$oldRoot];
        foreach ($joins as $k => $join) {

            if (false === strpos($join->getJoin(), '\\')) {
                $entity = stristr($join->getJoin(), '.', true);
                $field = substr(stristr($join->getJoin(), '.', false), 1);
                $alias = $join->getAlias();

                if (!in_array($join->getAlias(), array_keys($aliases))) {
                    $mappings = $em->getMetadataFactory()->getMetadataFor($entities[$entity])->getAssociationMappings();
                    $aliases[$join->getAlias() ] = $alias . '___' . str_replace('\\', '_', $mappings[$field]['targetEntity'] . '_');
                    $entities[$join->getAlias() ] = $mappings[$field]['targetEntity'];
                }
            } else {

                // for backside joins
                $entity = $join->getJoin();
                $field = stristr($join->getCondition(), '=', true);
                $field = trim(substr(stristr($field, '.', false), 1));
                $alias = $join->getAlias();
                if (!in_array($join->getAlias(), array_keys($aliases))) {
                    $mappings = $em->getMetadataFactory()->getMetadataFor($entity)->getAssociationMappings();
                    $aliases[$join->getAlias() ] = $alias . '___' . str_replace('\\', '_', $entity . '_');
                    $entities[$join->getAlias() ] = $entity;
                }
            }
        };

        foreach ($aliases as $k => $v) {

            // mark root
            if ($k == $oldRoot) {
                $v = '##';
            }
            $pattern = '/ ' . $k . '([,\. ])/';
            $replace = ' ' . $v . "$1";
            $dql = preg_replace($pattern, $replace, $dql);

            // for searches
            $pattern = '/CONCAT\(' . $k . '/';
            $replace = 'CONCAT(' . $v . "$1";
            $dql = preg_replace($pattern, $replace, $dql);
        }

        // remark root
        $dql = str_replace('##', $root, $dql);

        $g = $this->getGrid();
        $columns = [];

        foreach ($g->getColumns() as $k => $v) {;
            $oldAlias = $v->getAlias();
            $oldValue = $v->getValue();
            $oldOptions = $v->getOptions();

            // if the alias is missing from our query,
            // don't do all this other stuff
            if (isset($aliases[stristr($oldAlias, '_', true) ])) {

                // tilde mapping
                $newAlias = $aliases[stristr($oldAlias, '_', true) ] . '_' . $v->getValue();
                $oldAliases[str_replace('_', '.', $oldAlias) ] = $newAlias;
                $tildes = ['title', 'parentId', 'entityId'];

                foreach ($tildes as $k => $option) {
                    $newAlias = $aliases[stristr($oldAlias, '_', true) ] . '_' . $v->getValue();
                    if (isset($oldOptions[$option])) {
                        $oldOptions[$option] = $this->pregAlias($oldOptions[$option], $aliases);
                    }
                }

                if (isset($oldOptions['attr'])) {
                    foreach (array_keys($oldOptions['attr']) as $k => $option) {
                        $newAlias = $aliases[stristr($oldAlias, '_', true) ] . '_' . $v->getValue();

                        if (isset($oldOptions['attr'][$option])) {
                            $oldOptions['attr'][$option] = $this->pregAlias($oldOptions['attr'][$option], $aliases);
                        }
                    }
                }

                $columns[] = new Column($newAlias, $oldValue, $oldOptions);
            } else {
                $g->addError('Column \'' . $oldAlias . '\' maps to alias not present in query');
            }

            $g->setAliases($oldAliases);

            // if the column starts with a tilde, use a value from the field specified
            // this is when you have several objects of the same category
            // and you want that category to be the column header

            // e.g. a Fiscal Year label for a budgeting application

            // $gm->addField('g' . $k, 'value', array('title' => '~b' . $k . '.fiscalYear'));
            // in this case gridmaker would grab the value of b0.fiscalYear when it hydrated
            // the grid, making the column heading for g0.value column be the value b0.fiscalYear

            // the column must be specified as a hidden column in your grid.

        }

        foreach ($this->getGrid()->getActions() as $actionKey => $action) {
            if ($action->getRoute()) {
                if ('string' == gettype($action->getRoute())) {
                } else {
                    $routeConfig = $action->getRoute();
                    if (1 === count($routeConfig)) {
                        foreach ($routeConfig as $routeKey => $params) {
                            foreach ($params as $paramKey => $param) {
                                $routeConfig[$routeKey][$paramKey] = $this->pregAlias($param, $aliases);
                            }
                        }
                        $action->setRoute($routeConfig);
                    } else {
                        $this->addError('Action route improperly specified with more than one route.');
                    }
                }
            }
        }

        $g->setColumns($columns);

        return $dql;
    }

    public function aggregateQuery()
    {

        // we don't want to change the original query so we clone it.
        $qb = clone $this->queryBuilder;
        $qb->resetDQLPart('select');
        $qb->resetDQLPart('orderBy');
        foreach ($this->getGrid()->getColumns() as $key => $column) {
            if (!$column->getOption('hidden')) {
                if ($column->getOption('aggregate')) {
                    if (strpos($column->getOption('aggregate'), '#') !== false) {
                        $qb->addSelect('\'' . substr($column->getOption('aggregate'), 1) . '\'');
                    } else {
                        $qb->addSelect($column->getOption('aggregate'));
                    }
                } else {
                    $qb->addSelect('\'\'');
                }
            }
        }
        return $qb;
    }

    public function mapFieldsFromQB()
    {
        $qb = $this->getQB();
        $partials = [];
        foreach ($qb->getDQLParts() ['select'] as $select) {
            if (preg_match('|partial (.*?)\.\{(.*?)\}|', $select->getParts() [0], $matches)) {
                $partials[$matches[1]][] = $matches[2];
            } else {
                $partials[$select->getParts() [0]] = array('id');
            }
        }

        $entities = array_merge(
            array_map(
                function ($f) {return $f->getAlias();}, $qb->getDQLPart('from')),
            array_map(
                function ($f) {return $f->getAlias();},
                $qb->getDQLPart('join') [$qb->getDQLParts() ['from'][0]->getAlias() ])
            );

        // While addSelect just adds more
        foreach ($partials as $entity => $fields) {
            if ($qb->getRootAlias() == $entity) {
                if ($key = array_search('id', $fields)) {
                    unset($fields[$key]);
                }
                $qb->select('partial ' . $entity . '.{id,' . implode(',', $fields) . '}');
            } else {
            }
        }

        foreach ($partials as $entity => $fields) {
            if ($qb->getRootAlias() == $entity) {
            } else {
                if ($key = array_search('id', $fields)) {
                    unset($fields[$key]);
                }
                $qb->addSelect('partial ' . $entity . '.{id,' . implode(',', $fields) . '}');
            }
        }

        $this->getGrid()->newColumns();
        foreach ($partials as $entity => $fields) {
            foreach (explode(',', $fields[0]) as $k => $field) {
                $field = trim($field);
                $this->getGrid()->addColumn(new Column($entity . "_" . $field, $field));
            }
        }
    }

    public function mapFieldsFromColumns()
    {
        $qb = $this->getQB();

        $partials = [];
        $columns = $this->getGrid()->getColumns();

        foreach ($columns as $key => $column) {
            $partials[$column->getEntity() ][] = $column->getValue();
        }

        // Need to do the following loops twice because select removes all fields
        // While addSelect just adds more

        // This bit is to make sure added columns are added to the query as partials
        foreach ($partials as $entity => $fields) {
            if ($qb->getRootAlias() == $entity) {
                if ($key = array_search('id', $fields)) {
                    unset($fields[$key]);
                }
                $qb->select('partial ' . $entity . '.{id,' . implode(',', $fields) . '}');
            } else {
            }
        }

        foreach ($partials as $entity => $fields) {
            if ($qb->getRootAlias() == $entity) {
            } else {
                if ($key = array_search('id', $fields)) {
                    unset($fields[$key]);
                }
                $qb->addSelect('partial ' . $entity . '.{id,' . implode(',', $fields) . '}');
            }
        }
    }

    public function mapMethodsFromQB()
    {

        // $qb = $this->getQB();

        // $partials = [];
        // $methods = array_filter( $this->getGrid()->getColumns(),
        //     function( $c ) {
        //         return isset( $c->getOptions()['method'] ) && $c->getOptions()['method'];
        //     }
        // );

        // foreach ( $columns as $key => $column ) {
        //     $partials[$column->getEntity()][] = $column->getValue();
        // }

        // // Need to do the following loops twice because select removes all fields
        // // While addSelect just adds more

        // // This bit is to make sure added columns are added to teh query as partials
        // foreach ( $partials as $entity => $field ) {
        //     if ( $qb->getRootAlias() == $entity ) {
        //         $qb->select( 'partial '.$entity.'.{'.implode( ',', $field ).'}' );
        //     } else {
        //     }
        // }

        // foreach ( $partials as $entity => $field ) {
        //     if ( $qb->getRootAlias() == $entity ) {
        //     } else {
        //         $qb->addSelect( 'partial '.$entity.'.{'.implode( ',', $field ).'}' );
        //     }
        // }


    }

    public function addSearch($search)
    {
        $qb = $this->QB();
        $searchFields = array_map(function ($c)
        {
            return $c->getOption('search');
        }, array_filter($this->getGrid()->getColumns(), function ($c)
        {
            return $c->getOption('search');
        }));

        $searches = array();
        foreach ($searchFields as $field => $type) {
            $searches[$type][] = str_replace('_', '.', $field);
        }

        // $search is the explicit request from user
        // $searches are the fields for while the filter should be searched

        $numbers = (isset($searches['number']) && $searches['number']) ? $searches['number'] : array();
        $dates = (isset($searches['date']) && $searches['date']) ? $searches['date'] : array();
        $strings = (isset($searches['string']) && $searches['string']) ? $searches['string'] : array();

        if ($numbers == array() && $dates == array() && $strings == array()) {

            // just bail out if there are no fields to search in
            return $qb;
        }

        $search = explode(' ', $search);

        $search = array_filter($search, function ($e)
        {
            return !!$e;
        });

        if (array('') == $search || array() == $search) {

            // just bail out if there is nothing to search for
            return $qb;
        }

        foreach ($search as $key => $value) {
            $value = trim($value);
            $value = str_replace("'", "''", $value);
            $value = str_replace(",", "", $value);
            $value = str_replace(";", "", $value);
            $cqb = array();

            if ($strings != array()) {
                foreach ($strings as $stringKeys => $stringValues) {
                    $cqb[] = $qb->expr()->like("LOWER(CONCAT($stringValues, ''))", "'%" . strtolower($value) . "%'");
                }
            }

            if ($numbers != array()) {
                foreach ($numbers as $numberKeys => $numberValues) {
                    $cqb[] = $qb->expr()->like("CONCAT($numberValues, '')", "'%$value%'");
                }
            }

            if ($dates != array()) {
                foreach ($dates as $dateKeys => $dateValues) {
                    $cqb[] = $qb->expr()->like("LOWER(CONCAT($dateValues, ''))", "'%" . strtolower($value) . "%'");
                }
            }

            $qb->andWhere(call_user_func_array(array($qb->expr(), "orx"), $cqb));
        }

        return $qb;
    }

    public function addFilter($filter)
    {
        $qb = $this->QB();
        $filterFields = array_map(function ($c)
        {
            return $c->getOption('filter');
        }, array_filter($this->getGrid()->getColumns(), function ($c)
        {
            return $c->getOption('filter');
        }));

        $filters = array();
        foreach ($filterFields as $field => $type) {
            $filters[$type][] = str_replace('_', '.', $field);
        }

        // $filter is the explicit request from user
        // $filters are the fields for while the filter should be filtered

        $numbers = (isset($filters['number']) && $filters['number']) ? $filters['number'] : array();
        $dates = (isset($filters['date']) && $filters['date']) ? $filters['date'] : array();
        $strings = (isset($filters['string']) && $filters['string']) ? $filters['string'] : array();

        if ($numbers == array() && $dates == array() && $strings == array()) {

            // just bail out if there are no fields to filter in
            return $qb;
        }

        $filter = explode(';', $filter);

        foreach ($filter as $key => $filt) {
            $flt = explode(':', $filt);

            unset($filter[$key]);
            if (isset($flt[1])) {
                $filter[$flt[0]] = $flt[1];
            }
        }

        $filter = array_filter($filter, function ($e)
        {
            return !!$e;
        });

        if (array('') == $filter || array() == $filter) {

            // just bail out if there is nothing to filter for
            return $qb;
        }

        foreach ($filter as $key => $value) {
            $value = trim($value);
            $value = str_replace("'", "''", $value);
            $value = str_replace(",", "", $value);
            $value = str_replace(";", "", $value);
            $key = preg_replace('/___(.*?)__/', '.', $key);
            if ($value == 'null') {
                $qb->andWhere($qb->expr()->isNull($key));
            } else {
                if (in_array($key, $numbers)) {
                    $qb->andWhere($qb->expr()->like("CONCAT($key, '')", "'%" . strtolower($value) . "%'"));
                } else {
                    $qb->andWhere($qb->expr()->like("LOWER(CONCAT($key, ''))", "'%" . strtolower($value) . "%'"));
                }
            }
        }
        return $qb;
    }

    public function setExport($export = true) {
        $this->getGrid()->setExport($export);
    }
}
