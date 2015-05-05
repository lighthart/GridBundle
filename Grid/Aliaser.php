<?php
namespace Lighthart\GridBundle\Grid;

class Aliaser
{
    /**
     * @var Lighthart\GridBundle\Grid\Grid
     */
    private $grid;
    /**
     * @var Doctrine\ORM\QueryBuilder
     */
    private $qb;
    /**
     * @var Array
     */
    private $aliases;
    /**
     * Get grid.
     *
     * @return
     */
    public function getGrid()
    {
        return $this->grid;
    }
    /**
     * Set grid.
     *
     * @param
     *
     * @return $this
     */
    public function setGrid($grid)
    {
        $this->grid = $grid;

        return $this;
    }
    /**
     * Get QB.
     *
     * @return
     */
    public function getQB()
    {
        return $this->qb;
    }

    public function QB()
    {
        return $this->qb;
    }

    /**
     * Set qb.
     *
     * @param
     *
     * @return $this
     */
    public function setQB($qb)
    {
        $this->qb = $qb;

        return $this;
    }

    /**
     * Get aliases.
     *
     * @return
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Set aliases.
     *
     * @param
     *
     * @return $this
     */
    public function setAliases($aliases)
    {
        $this->aliases = $aliases;

        return $this;
    }

    public function __construct($grid = null, $qb = null)
    {
        if (!$grid) {
            throw new \Exception('Aliaser invoked without grid');
        }
        if (!$qb) {
            throw new \Exception('Aliaser invoked without qb');
        }
        $this->grid = $grid;
        $this->qb   = $qb;
        $this->determineAliases();
    }

    public function determineAliases()
    {
        $dql           = $this->qb->getQuery()->getDQL();
        $aliases       = [];
        $entities      = [];
        $from          = $this->qb->getDqlPart('from') [0];

        $em = $this->qb->getEntityManager();
        $em->getMetadataFactory()->getAllMetadata();

        // right now I am not supporting more than one from clause.
        // I don't even know what more than one from cluase means.
        $entities[$from->getAlias()] = $from->getFrom();

        $joins = $this->qb->getDqlPart('join') [$from->getAlias()];
        foreach ($joins as $k => $join) {
            if (false === strpos($join->getJoin(), '\\')) {
                $entity = stristr($join->getJoin(), '.', true);
                $field  = substr(stristr($join->getJoin(), '.', false), 1);
                if (!in_array($join->getAlias(), array_keys($aliases))) {
                    $mappings                     = $em->getMetadataFactory()->getMetadataFor($entities[$entity])->getAssociationMappings();
                    if ($mappings[$field]['type'] <= 2) {
                        // $qb->addGroupBy($alias);
                    }
                    $entities[$join->getAlias()] = $mappings[$field]['targetEntity'];
                }
            } else {
                // for backside joins
                $entity = $join->getJoin();
                $field  = stristr($join->getCondition(), '=', true);
                $field  = trim(substr(stristr($field, '.', false), 1));
                if (!in_array($join->getAlias(), array_keys($aliases))) {
                    $mappings                     = $em->getMetadataFactory()->getMetadataFor($entity)->getAssociationMappings();
                    if ($mappings[$field]['type'] <= 2) {
                        // $qb->addGroupBy($alias);
                    }
                    $entities[$join->getAlias()] = $entity;
                }
            }
        };
        array_map(function ($class, $alias) use (&$aliases) {
            $aliases[$alias] = str_replace('\\', '_', $class . '_');
        }, $entities, array_keys($entities));

        $this->aliases = $aliases;
    }
}
