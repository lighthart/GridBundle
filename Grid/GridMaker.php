<?php
namespace Lighthart\GridBundle\Grid;

// use Knp\Component\Pager\Paginator;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class GridMaker
{
    private $doctrine;
    private $router;
    private $request;
    private $dql;
    private $query;
    private $queryBuilder;
    private $grid;

    /**
     * This should never be used -- method is so there is not an exception thrown
     * @return string
     */
    public function __toString()
    {
        return "Grid Maker -- Don't print this";
    }

    /**
     * Dependency injection constructor
     * @param Doctrine Service
     * @param Router Service
     */
    public function __construct($doctrine, $router)
    {
        $this->doctrine = $doctrine;
        $this->router   = $router;
    }

    /**
     * Getter Method
     * @return Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Setter Method
     * @param Request
     * @return self
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Getter Method
     * @return Grid
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * Setter Method
     * @param Grid
     * @return self
     */
    public function setGrid(Grid $grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * Over write existing grid with blank one
     * @return self
     */
    public function newGrid()
    {
        $this->grid = new Grid();

        return $this;
    }

    /**
     * Getter Method-- This allows a DQL over write of the queryBuilder's query
     * @return Grid
     */
    public function getDQL()
    {
        if ($this->dql) {
            return $this->dql;
        } else {
            return $this->queryBuilder->getQuery()->getDQL();
        }
    }

    /**
     * Setter Method
     * @return self
     */
    public function setDQL($dql)
    {
        $this->dql = $dql;

        return $this;
    }

    /**
     * Getter Method
     * @return Doctrine\ORM\Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Setter Method
     * @return self
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Getter Method - redundant shortcut
     * @return Doctrine\ORM\Query
     */
    public function Q()
    {
        return $this->getQuery();
    }

    /**
     * Getter Method - redundant shortcut
     * @return Doctrine\ORM\Query
     */
    public function getQ()
    {
        return $this->getQuery();
    }

    /**
     * Setter Method - redundant shortcut
     * @return self
     */
    public function setQ($query)
    {
        $this->setQuery($query);

        return $this;
    }

    /**
     * Getter Method
     * @return Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * Setter Method
     * @return self
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;

        return $this;
    }

    /**
     * Getter/Setter Method - redundant shortcut
     * @return Doctrine\ORM\QueryBuilder
     */
    public function QB($queryBuilder = null)
    {
        if ($queryBuilder) {
            $this->setQueryBuilder($queryBuilder);

            return $this;
        } else {
            return $this->getQueryBuilder();
        }
    }

    /**
     * Getter Method - redundant shortcut
     * @return Doctrine\ORM\QueryBuilder
     */
    public function getQB()
    {
        return $this->getQueryBuilder();
    }

    /**
     * Setter Method - redundant shortcut
     * @return self
     */
    public function setQB($queryBuilder)
    {
        return $this->setQueryBuilder($queryBuilder);
    }

    /**
     * [initialize description]
     * @param  array
     * @return [type]
     */
    public function initialize($options = [])
    {
        $this->grid = new Grid($options);
        $this->grid->setRouter($this->router);
    }

    /**
     * Determines class is present in ORMs.  Returns array, sets grid errors if
     * metadata is not present
     *
     * Slash boolean controls whether to interpret as underscore encoded class
     * encoded class switches backslashes (\) for underscores (_) so function may
     * be used to generate a url
     * @param  String classname
     * @param  Boolean slash
     * @return Array
     */
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
            $error .= 'Class for grid verify not specified';
        }

        try {
            $metadata = $metadataFactory->getMetadataFor($backslash);
        } catch (\Exception $ex) {
            $metadata = null;
            $error .= 'No metadata for class: ' . $backslash;
        }

        if ($error != '') {
            $error = 'grid.maker error: ' . $error;
        }

        if ($metadata) {
            return [
                'class'    => $class,
                'metadata' => $metadata,
                'error'    => null,
            ];
        } else {
            [
                'class'    => null,
                'metadata' => null,
                'error'    => $error,
            ];
        }
    }

    /**
     * Adds a column based on an entity field
     * Actions will be part of their own column
     * @param self
     */
    public function addField($entity, $value = 'id', array $options = [])
    {
        if ('id' == $value) {
            if (in_array($entity . '_' . $value, array_keys($this->getGrid()->getColumns()))) {
                return $this;
            } else {
                $this->getGrid()->addColumn(new Column($entity . '_' . $value, $value, $options));

                return $this;
            }
        }

        $this->addField($entity, 'id', [
            'hidden' => true,
        ]);
        $this->getGrid()->addColumn(new Column($entity . '_' . $value, $value, $options));

        return $this;
    }

    /**
     * In progress --- meant to return a function call based on entity code which is more than just
     * getter/setter
     * @param [type]
     * @param [type]
     * @param array
     */
    public function addMethod($entity, $method, array $options = [])
    {
        if (method_exists($entity, $method)) {
            $this->getGrid()->addMethod(new Column($entity, $method, $options));
        }
    }

    /**
     * Adds an action
     * Actions will be part of their own column
     * @param self
     */
    public function addAction($options)
    {
        $this->getGrid()->addAction(new Action($options));

        return $this;
    }

    /**
     * Adds a status
     * Statuses will be part of their own column
     * @param self
     */
    public function addStatus($options)
    {
        $this->getGrid()->addStatus(new Status($options));

        return $this;
    }

    /**
     * Some of hydrate Grid excised here
     */

    public function paginateGridFromCookies(Request $request, $options = [])
    {
        $cookies    = $request->cookies;
        $pageSize   = $request->cookies->get('lg-results-per-page') ?: 10;
        $pageOffset = $request->cookies->get("lg-" . $request->attributes->get('_route') . "-offset");
        $search     = $request->cookies->get("lg-" . $request->attributes->get('_route') . "-search");
        $filter     = $request->cookies->get("lg-" . $request->attributes->get('_route') . "-filter");
        $sort       = $request->cookies->get("lg-" . $request->attributes->get('_route') . "-sort");
        $this->addFilter($filter);
        $this->addSearch($search);
        $cqb  = clone $this->QB();
        $root = $cqb->getDQLPart('from') [0]->getAlias() . ".id";
        $cqb->resetDQLPart('orderBy');
        $cqb->setMaxResults(null);
        $cqb->setFirstResult(null);
        $cqb->select($cqb->expr()->count($root));
        $cqb->distinct();
        $cqb->resetDQLPart('groupBy');
        $cq = $cqb->getQuery();
        $cq->setDql($this->mapAliases(['qb' => $cqb]));

        // print_r('<pre>');
        // print_r($cqb->getQuery()->getDQL());
        // die;

        $this->getGrid()->setTotal($cq->getSingleScalarResult());

        $offset = ($request->query->get('pageOffset') ?: ($pageOffset ?: 0));
        $offset = ($offset > $this->getGrid()->getTotal()) ? $offset = $this->getGrid()->getTotal() - $pageSize : $offset;
        $offset = ($offset < 0) ? 0 : $offset;
        $offset = floor($offset / $pageSize) * $pageSize;
        $this->getGrid()->setPageSize($pageSize);
        $this->getGrid()->setOffset($offset);
        $this->QB()->setFirstResult($offset);
        $this->QB()->setMaxResults($pageSize);

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

        $this->getGrid()->setSearch($search);
    }

    /**
     * Heavy Lifter.  Possible candidate for further encapsulation
     * return differing based on export or not suggests should be broken up
     *
     * Takes the request, gets query parameters based on cookies,
     * modifies request based on pagination
     *
     * Makes count query
     *
     * Calls rewrite methods which modify query to just partials
     *
     * Creates export file if appropriate
     *
     * Calls row filling methods
     *
     * @param  Request
     * @param  array
     * @return self of response
     */

    public function hydrateGrid(Request $request, $options = [])
    {
        set_time_limit(0);
        $defaultOptions = [
            'fromQB' => false,
            'result' => false,
        ];
        $options = array_merge($defaultOptions, $options);
        $fromQB  = $options['fromQB'];
        $results = $options['result'];
        $export   = $request->query->get('export');
        $debug   = $request->query->get('debug');

        // this is for displaying filter boxes
        $filters = !!$request->cookies->get('lg-filter-toggle');

        // this is for autogeneration of grid from QB instead of column specifications
        // it is not really built as of Dec 2014
        if ($fromQB) {
            $this->mapFieldsFromQB();
        } else {
            $this->mapFieldsFromColumns();
        }

        // this is for autogeneration of grid from QB instead of column specifications
        // it is not really built as of Dec 2014
        // $this->mapMethodsFromQB();

        if ($this->getGrid()->getOption('singlePage')) {
        }

        $this->paginateGridFromCookies($request, $options);
        if ('all' == strtolower($export)){
            $export = strtolower($export);
        } else {
            $export = intval($export);
        }

        $this->QB()->distinct();

        if ($export) {
            $offset   = 0;
            if ('all' == $export) {
                $pageSize = 500;
            } else {
                $pageSize = 500 < $export ? 500 : $export;
            }

            $this->QB()->setFirstResult($offset);
            $this->QB()->setMaxResults($pageSize);

            $now          = new \DateTime();
            $now          = $now->format('Ymdhis');
            $micro        = substr(explode(" ", microtime()) [0], 2, 6);
            $filename     = 'export' . $now . $micro . '.csv';
            $fullfilename = '/tmp/' . $filename;
            $file         = fopen($fullfilename, 'w');

            $this->QB()->setFirstResult($offset);
            if ($results) {
                fputcsv($file, $this->getGrid()->exportTh());
                foreach ($this->getGrid()->exportTr($results) as $key => $line) {
                    fputcsv($file, $line);
                }
            } else {
                fputcsv($file, $this->getGrid()->exportTh());
                $results = $this->QB()->getQuery()->getResult(Query::HYDRATE_SCALAR);
                while (([] != $results) && ('all' == $export || $offset < $export)) {
                    $this->QB()->setFirstResult($offset);
                    $results = $this->QB()->getQuery()->getResult(Query::HYDRATE_SCALAR);
                    $offset += $pageSize;

                    // Write this next line to file
                    foreach ($this->getGrid()->exportTr($results) as $key => $line) {
                        fputcsv($file, $line);
                    }
                }
            }

            fclose($file);

            // $response = new Response();

            $response = new BinaryFileResponse($fullfilename);
            $d        = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
            $response->headers->set('Content-Disposition', $d);

            return $response;
        } else {
            $q = $this->QB()->getQuery();
            if ($results) {
                // This should be handled in controller for now... need a way to smooth this out
                // $q->setDql($this->mapAliases(['results' => $results]));
            } else {
                $q->setDql($this->mapAliases());
                $results = $q->getResult(Query::HYDRATE_SCALAR);
                // var_dump($results);
                // die;
            }

            $this->mapActions();
            $this->mapColumns();
            $results = $this->mapResults($results);

            if ([] == $results) {
                $root = 'root';
            } else {
                $root = preg_grep('/^root\_\_\_(.*?)\_\_id$/', array_keys($results[0]));

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

                $sums = array_filter($this->getGrid()->getColumns(), function ($c) {
                    return in_array('aggregate', array_keys($c->getOptions()));
                });

                if ([] !== $results && [] != $sums) {
                    $this->getGrid()->fillAggregate($this->aggregateQuery());
                } else {
                }
            }
        }

        return $this;
    }

    public function pregAlias($alias, $aliases)
    {
        if (preg_match('/(.*?)~(((.*?)~)+)(.*)/', $alias, $match)) {
            $matches = array_filter(explode('~', $match[2]));
            foreach ($matches as $key => $col) {
                if (preg_match('/\<(.*?)\>/', $col)) {
                } else {
                    $fields = explode('|', $col);
                    foreach ($fields as $fieldKey => $field) {
                        $oldField    = substr(stristr($field, '.'), 1);
                        $oldSubAlias = stristr($field, '.', true);
                        if (false !== $oldSubAlias) {
                            if (isset($aliases[$oldSubAlias])) {
                                $fields[$fieldKey] = $aliases[$oldSubAlias].'_'.$oldField;
                            } elseif (isset($aliases[$field])) {
                                // why is this needed? something is not getting mapped properly
                                $fields[$fieldKey] = $aliases[$field];
                            } else {
                                throw new \Exception('Column alias does not match query alias: ' . $oldSubAlias . ' is in error');
                            }
                             // . '_' . $oldField;
                        }
                    }
                }
            }
            $alias = $match[1] . '~' . implode('|', $fields) . '~' . $match[5];
        }
        return $alias;
    }

    public function mapAliases(array $options = [])
    {
        $defaultOptions = ['result' => false, 'qb' => false ];
        $options        = array_merge($defaultOptions, $options);
        $result         = $options['result'];
        $qb             = $options['qb'];

        // This function converts all the HYDRATE_SCALAR
        // column headings to contain classname
        $newResult = [];
        if ($qb) {
        } else {
            $qb = $this->queryBuilder;
        }

        $dql = $qb->getQuery()->getDQL();

        $aliases       = [];
        $from          = $qb->getDqlPart('from') [0];
        $rootClassPath = $from->getFrom();
        $oldRoot       = $qb->getRootAlias();

        // mark root
        $root               = 'root___' . str_replace('\\', '_', $rootClassPath . '_');
        $aliases[$oldRoot]  = $root;
        $entities[$oldRoot] = $rootClassPath;
        $rootSelect         = array_filter($qb->getDqlPart('select'), function ($s) {
            return $s->getParts()[0] == 'partial root.{id}';
        });
        $qb->addGroupBy('root');

        if (isset($rootSelect[0]) && $rootSelect[0]) {
            // escaping the count query
            $rootSelectParts = explode(',', substr(stristr(stristr($rootSelect[0], '{'), '}', true), 1));

            foreach ($rootSelectParts as $k => $v) {
                $oldAliases['root.' . $v] = $root . '_' . $v;
            }
        }

        // rewrite rootaliases in result
        //
        if (is_array($result)) {
            foreach ($result as $keyResult => $valueResult) {
                foreach ($valueResult as $keySingle => $valueSingle) {
                    if (strpos($keySingle, 'root_') !== false) {
                        $valueResult[$root . substr(strstr($keySingle, 'root_'), 4) ] = $valueResult[$keySingle];
                        unset($valueResult[$keySingle]);
                    }
                }
                $result[$keyResult] = $valueResult;
            }
        }

        $em = $qb->getEntityManager();
        $em->getMetadataFactory()->getAllMetadata();

        // realiased qb

        $rqb = $em->getRepository($rootClassPath)->createQueryBuilder($root);
        // if ($count) {
        //     $rqb->select($rqb->expr()->count($root));
        //     $rqb->distinct();
        // }

        $joins = $qb->getDqlPart('join') [$oldRoot];
        foreach ($joins as $k => $join) {
            if (false === strpos($join->getJoin(), '\\')) {
                $entity = stristr($join->getJoin(), '.', true);
                $field  = substr(stristr($join->getJoin(), '.', false), 1);
                $alias  = $join->getAlias();
                if (!in_array($join->getAlias(), array_keys($aliases))) {
                    $mappings                     = $em->getMetadataFactory()->getMetadataFor($entities[$entity])->getAssociationMappings();
                    if ($mappings[$field]['type'] <= 2 ) {
                        $qb->addGroupBy($alias);
                    }
                    $aliases[$join->getAlias() ]  = $alias . '___' . str_replace('\\', '_', $mappings[$field]['targetEntity'] . '_');
                    $entities[$join->getAlias() ] = $mappings[$field]['targetEntity'];
                }
            } else {
                // for backside joins
                $entity = $join->getJoin();
                $field  = stristr($join->getCondition(), '=', true);
                $field  = trim(substr(stristr($field, '.', false), 1));
                $alias  = $join->getAlias();
                if (!in_array($join->getAlias(), array_keys($aliases))) {
                    $mappings                     = $em->getMetadataFactory()->getMetadataFor($entity)->getAssociationMappings();

                    if ($mappings[$field]['type'] <= 2 ) {
                        $qb->addGroupBy($alias);
                    }
                    $aliases[$join->getAlias() ]  = $alias . '___' . str_replace('\\', '_', $entity . '_');
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
            $dql     = preg_replace($pattern, $replace, $dql);

            // for searches
            $pattern = '/CONCAT\(' . $k . '/';
            $replace = 'CONCAT(' . $v . "$1";
            $dql     = preg_replace($pattern, $replace, $dql);

            $pattern = '/\(' . $k . '(\..*?)\)/';
            $replace = '(' . $v . '$1)';
            $dql     = preg_replace($pattern, $replace, $dql);
        }

        // remark root
        $dql = str_replace('##', $root, $dql);

        $g = $this->getGrid();
// need to add root.id here
        foreach ($g->getColumns() as $k => $v) {
            $oldAlias   = $v->getAlias();
            $oldValue   = $v->getValue();
            $oldOptions = $v->getOptions();

            // if the alias is missing from our query,
            // don't do all this other stuff
            if (isset($aliases[stristr($oldAlias, '_', true) ])) {
                // tilde mapping
                $newAlias                                      = $aliases[stristr($oldAlias, '_', true) ] . '_' . $v->getValue();
                $oldAliases[str_replace('_', '.', $oldAlias) ] = $newAlias;
                $tildes                                        = ['title', 'parentId', 'entityId', 'value'];

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

                if ($result) {
                    foreach ($result as $keyResult => $valueResult) {
                        if (array_key_exists($oldAlias, $result[$keyResult])) {
                            $result[$keyResult][$newAlias] = $result[$keyResult][$oldAlias];
                            unset($result[$keyResult][$oldAlias]);
                        }
                    }
                }
            } else {
                $g->addError('Column \'' . $oldAlias . '\' maps to alias not present in query');
            }
            // if the column starts with a tilde, use a value from the field specified
            // this is when you have several objects of the same category
            // and you want that category to be the column header

            // e.g. a Fiscal Year label for a budgeting application

            // $gm->addField('g' . $k, 'value', array('title' => '~b' . $k . '.fiscalYear'));
            // in this case gridmaker would grab the value of b0.fiscalYear when it hydrated
            // the grid, making the column heading for g0.value column be the value b0.fiscalYear

            // the column must be specified as a hidden column in your grid.
        }
        $g->setAliases($oldAliases);
    }

    public function mapColumns()
    {
        $aliases = $this->getGrid()->getAliases();
        $columns = [];
        $g       = $this->getGrid();
        foreach ($g->getColumns() as $k => $v) {
            $oldAlias   = $v->getAlias();
            $oldValue   = $v->getValue();
            $oldOptions = $v->getOptions();

            $tildes     = ['title', 'parentId', 'entityId', 'value'];

            foreach ($tildes as $k => $option) {
                $newAlias = $aliases[str_replace('_', '.', $oldAlias)];
                if (isset($oldOptions[$option])) {
                    $oldOptions[$option] = $this->pregAlias($oldOptions[$option], $aliases);
                }
            }

            foreach ($oldOptions as $optionKey => $optionValue) {
                $newAlias   = $g->getAliases()[str_replace('_', '.', $oldAlias)];
                $columns[]  = new Column($newAlias, $oldValue, $oldOptions);
            }
        }

        $g->setColumns($columns);
    }

    public function mapResults(array $result)
    {
        $newResult = [];
        $g         = $this->getGrid();
        foreach ($result as $key => $value) {
            foreach ($value as $k => $v) {
                $newKey                                     = str_replace('_', '.', $k);
                $newResult[$key][$g->getAliases()[$newKey]] = $v;
            }
        }

        return $newResult;
    }

    public function mapActions()
    {
        foreach ($this->getGrid()->getActions() as $actionKey => $action) {
            if ($action->getRoute()) {
                if ('string' == gettype($action->getRoute())) {
                } else {
                    $routeConfig = $action->getRoute();
                    if (1 === count($routeConfig)) {
                        foreach ($routeConfig as $routeKey => $params) {
                            foreach ($params as $paramKey => $param) {
                                $routeConfig[$routeKey][$paramKey] = $this->pregAlias($param, $this->getGrid()->getAliases());
                            }
                        }
                        $action->setRoute($routeConfig);
                    } else {
                        $this->addError('Action route improperly specified with more than one route.');
                    }
                }
            }
        }
    }

    public function mapStatuses()
    {
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
        $qb       = $this->getQB();
        $partials = [];
        foreach ($qb->getDQLParts() ['select'] as $select) {
            if (preg_match('|partial (.*?)\.\{(.*?)\}|', $select->getParts() [0], $matches)) {
                $partials[$matches[1]][] = $matches[2];
            } else {
                $partials[$select->getParts() [0]] = [
                    'id',
                ];
            }
        }

        $entities = array_merge(array_map(function ($f) {
            return $f->getAlias();
        }, $qb->getDQLPart('from')), array_map(function ($f) {
            return $f->getAlias();
        }, $qb->getDQLPart('join') [$qb->getDQLParts() ['from'][0]->getAlias() ]));

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
        $columns  = $this->getGrid()->getColumns();

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
                $qb->select('partial ' . $entity . '.{' . implode(',', $fields) . '}');
            } else {
            }
        }

        foreach ($partials as $entity => $fields) {
            if ($qb->getRootAlias() == $entity) {
            } else {
                if ($key = array_search('id', $fields)) {
                    unset($fields[$key]);
                }
                $qb->addSelect('partial ' . $entity . '.{' . implode(',', $fields) . '}');
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
        $qb           = $this->QB();
        $searchFields = array_map(function ($c) {
            return $c->getOption('search');
        }, array_filter($this->getGrid()->getColumns(), function ($c) {
            return $c->getOption('search');
        }));

        $searches = [];
        foreach ($searchFields as $field => $type) {
            $searches[$type][] = str_replace('_', '.', $field);
        }

        // $search is the explicit request from user
        // $searches are the fields for while the filter should be searched

        $numbers = (isset($searches['number']) && $searches['number']) ? $searches['number'] : [];
        $dates   = (isset($searches['date']) && $searches['date']) ? $searches['date'] : [];
        $strings = (isset($searches['string']) && $searches['string']) ? $searches['string'] : [];

        if ($numbers == [] && $dates == [] && $strings == []) {
            // just bail out if there are no fields to search in
            return $qb;
        }

        $search = explode(' ', $search);

        $search = array_filter($search, function ($e) {
            return !!$e;
        });

        if ([
            '',
        ] == $search || [] == $search) {
            // just bail out if there is nothing to search for
            return $qb;
        }

        foreach ($search as $key => $value) {
            $value = trim($value);
            $value = str_replace("'", "''", $value);
            $value = str_replace(",", "", $value);
            $value = str_replace(";", "", $value);
            $cqb   = [];

            if ($strings != []) {
                foreach ($strings as $stringKeys => $stringValues) {
                    $cqb[] = $qb->expr()->like("LOWER(CONCAT($stringValues, ''))", "'%" . strtolower($value) . "%'");
                }
            }

            if ($numbers != []) {
                foreach ($numbers as $numberKeys => $numberValues) {
                    $cqb[] = $qb->expr()->like("CONCAT($numberValues, '')", "'%$value%'");
                }
            }

            if ($dates != []) {
                foreach ($dates as $dateKeys => $dateValues) {
                    $cqb[] = $qb->expr()->like("LOWER(CONCAT($dateValues, ''))", "'%" . strtolower($value) . "%'");
                }
            }

            $qb->andWhere(call_user_func_array([
                $qb->expr(),
                "orx",
            ], $cqb));
        }

        return $qb;
    }

    public function addFilter($filter)
    {
        $qb           = $this->QB();
        $filterFields = array_map(function ($c) {
            return $c->getOption('filter');
        }, array_filter($this->getGrid()->getColumns(), function ($c) {
            return $c->getOption('filter');
        }));

        $filters = [];
        foreach ($filterFields as $field => $type) {
            $filters[$type][] = str_replace('_', '.', $field);
        }

        // $filter is the explicit request from user
        // $filters are the fields for while the filter should be filtered

        $numbers = (isset($filters['number']) && $filters['number']) ? $filters['number'] : [];
        $dates   = (isset($filters['date']) && $filters['date']) ? $filters['date'] : [];
        $strings = (isset($filters['string']) && $filters['string']) ? $filters['string'] : [];

        if ($numbers == [] && $dates == [] && $strings == []) {
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

        $filter = array_filter($filter, function ($e) {
            return !!$e;
        });

        if ([
            '',
        ] == $filter || [] == $filter) {
            // just bail out if there is nothing to filter for
            return $qb;
        }

        foreach ($filter as $key => $value) {
            $value = trim($value);
            $value = str_replace("'", "''", $value);
            $value = str_replace(",", "", $value);
            $value = str_replace(";", "", $value);
            $key   = preg_replace('/___(.*?)__/', '.', $key);
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

    public function setExport($export = 1000)
    {
        $this->getGrid()->setExport($export);
        return $this;
    }

    public function getExport()
    {
        return $this->getGrid()->getExport();
    }

}
