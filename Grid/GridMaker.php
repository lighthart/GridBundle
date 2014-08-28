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
    private $request;
    private $dql;
    private $query;
    private $queryBuilder;
    private $grid;

    public function __toString()
    {
        return "Grid Maker -- Don't print this";
    }

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getManager();
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

    public function initialize($attr = array())
    {
        $this->grid = new Grid(array(
            'attr' => $attr
        ));
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

    public function addField($entity, $value = 'id', array $options = array())
    {
        $this->getGrid()->addColumn(new Column($entity . '_' . $value, $value, $options));

        // var_dump($options);

    }

    public function addMethod($entity, $method, array $options = array())
    {
        if (method_exists($entity, $method)) {
            $this->getGrid()->addMethod(new Column($entity, $method, $options));
        }
    }

    public function hydrateGrid($fromQB = false)
    {

        if ($fromQB) {
            $this->mapFieldsFromQB();
        } else {
            $this->mapFieldsFromColumns();
        }

        $this->mapMethodsFromQB();
        $q = $this->getQueryBuilder()->getQuery()->setDql($this->mapAliases());
        $results = $q->getResult(Query::HYDRATE_SCALAR);

        $attr = $this->getGrid()->getTable()->getAttr();
        if (isset($attr['html']) && $attr['html']) {
            $this->getGrid()->fillTh($results);
            $this->getGrid()->fillTr($results);
        }
    }

    public function mapAliases($q = null)
    {
        $qb = $this->queryBuilder;

        if (null == $q) {
            $dql = $this->getDQL();
        } else {
            $dql = $q->getDQL();
        }

        $aliases = [];
        $from = $qb->getDqlPart('from') [0];
        $rootClassPath = $from->getFrom();
        $oldRoot = $qb->getRootAlias();
        $root = 'root___'.str_replace('\\', '_', $rootClassPath . '_');
        $aliases[$oldRoot] = $root;
        $entities[$oldRoot] = $rootClassPath;


        $em = $qb->getEntityManager();
        $em->getMetadataFactory()->getAllMetadata();

        // realiased qb

        $rqb = $em->getRepository($rootClassPath)->createQueryBuilder($root);

        $joins = $qb->getDqlPart('join') [$oldRoot];
        foreach ($joins as $k => $join) {
            $entity = stristr($join->getJoin() , '.', true);
            $field = substr(stristr($join->getJoin() , '.', false),1);
            $alias = $join->getAlias();

            if (!in_array($join->getAlias(), array_keys($aliases))) {
                $mappings = $em->getMetadataFactory()->getMetadataFor($entities[$entity])->getAssociationMappings();
                $aliases[$join->getAlias()] = $alias.'___'.str_replace('\\', '_', $mappings[$field]['targetEntity'] . '_');
                $entities[$join->getAlias()] = $mappings[$field]['targetEntity'];

            }
        }

        foreach ($aliases as $k => $v) {
            $pattern = '/ ' . $k . '([,. ])/';
            $replace = ' ' . $v . "$1";
            $dql = preg_replace($pattern, $replace, $dql);
        }

        $g = $this->getGrid();
        $columns = [];

        foreach ($g->getColumns() as $k => $v) {
            $oldAlias = $v->getAlias();
            $oldValue = $v->getValue();
            $oldOptions = $v->getOptions();
            $newAlias = $aliases[stristr($oldAlias,'_',true)] . '_' . $v->getValue();
            $columns[] = new Column($newAlias, $oldValue, $oldOptions);
            $g->setColumns($columns);
        }

        return $dql;
    }

    public function mapFieldsFromQB()
    {
        $qb = $this->getQB();
        $partials = [];
        foreach ($qb->getDQLParts() ['select'] as $select) {
            if (preg_match('|partial (.*?)\.\{(.*?)\}|', $select->getParts() [0], $matches)) {
                $partials[$matches[1]][] = $matches[2];
            } else {
                $partials[$select->getParts() [0]] = array(
                    'id'
                );
            }
        }

        $entities = array_merge(array_map(function ($f)
        {
            return $f->getAlias();
        }
        , $qb->getDQLPart('from')) , array_map(function ($f)
        {
            return $f->getAlias();
        }
        , $qb->getDQLPart('join') [$qb->getDQLParts() ['from'][0]->getAlias() ]));

        // While addSelect just adds more
        foreach ($partials as $entity => $fields) {
            if ($qb->getRootAlias() == $entity) {
                $fieldList = implode(',', array_merge(array_values($fields)));
                if (false === strpos($fields, 'id')) {
                    $fields.= ', id';
                }
                $qb->select('partial ' . $entity . '.{' . $fields . '}');
            } else {
            }
        }

        foreach ($partials as $entity => $fields) {
            if ($qb->getRootAlias() == $entity) {
            } else {
                $fieldList = implode(',', $fields);
                if (false === strpos($fields, 'id')) {
                    $fields.= ', id';
                }
                $qb->addSelect('partial ' . $entity . '.{' . implode(',', $fields) . '}');
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

        // This bit is to make sure added columns are added to teh query as partials
        foreach ($partials as $entity => $field) {
            if ($qb->getRootAlias() == $entity) {
                $fields = implode(',', $field);
                if (false === strpos($fields, 'id')) {
                    $fields.= ', id';
                }
                $qb->select('partial ' . $entity . '.{' . $fields . '}');
            } else {
            }
        }

        foreach ($partials as $entity => $field) {
            if ($qb->getRootAlias() == $entity) {
            } else {
                $fields = implode(',', $field);
                if (false === strpos($fields, 'id')) {
                    $fields.= ', id';
                }
                $qb->addSelect('partial ' . $entity . '.{' . $fields . '}');
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
}
