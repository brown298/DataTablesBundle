<?php
namespace Brown298\DataTablesBundle\Service\Processor;

use Brown298\DataTablesBundle\Model\RequestParameterBag;
use Brown298\DataTablesBundle\Model\ResponseParameterBag;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;

/**
 * Class QueryBuilderProcessor
 *
 * handles processing of the server side for a query builder
 *
 * @package Brown298\DataTablesBundle\Service\Processor
 * @author  John Brown <brown.john@gmail.com>
 */
class QueryBuilderProcessor extends AbstractProcessor implements ProcessorInterface
{
    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $queryBuilder;


    /**
     * __construct
     *
     * @param QueryBuilder $queryBuilder
     * @param RequestParameterBag $requestParameters
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        QueryBuilder        $queryBuilder,
        RequestParameterBag $requestParameters,
        LoggerInterface     $logger = null
    ) {
        parent::__construct($requestParameters, $logger);
        $this->setQueryBuilder($queryBuilder);
    }

    /**
     * process
     *
     * @param ResponseParameterBag $responseParameters
     * @param bool                 $getEntity
     *
     * @return ResponseParameterBag
     */
    public function process(ResponseParameterBag $responseParameters = null, $getEntity = false)
    {
        if ($responseParameters === null) {
            $responseParameters = new ResponseParameterBag();
            $responseParameters->setRequest($this->requestParameters);
        }

        $qb      = $this->buildQuery();
        $aliases = $qb->getRootAliases();
        $alias   = $aliases[0];

        if ($getEntity) {
            $responseParameters->setData($qb->getQuery()->getResult());
        } else {
            $responseParameters->setData($qb->getQuery()->getArrayResult());
        }
        $total        = $this->getTotalRecords(clone($this->queryBuilder), $alias);
        $displayTotal = $this->getTotalRecords(clone($this->queryBuilder), $alias);

        $responseParameters->setTotal($total);
        $responseParameters->setDisplayTotal($displayTotal);

        return $responseParameters;
    }

    /**
     * setQueryBuilder
     *
     * @param QueryBuilder $queryBuilder]
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        $this->parseColumns($queryBuilder);
    }

    /**
     * getQueryBuilder
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * parseColumns
     *
     * builds an array of the columns
     *
     * @param QueryBuilder $qb
     */
    public function parseColumns(QueryBuilder $qb)
    {
        $selects = $qb->getDQLPart('select');
        $this->setColumns(array());

        if (!empty($selects)) {
            foreach ($selects as $queryPart) {
                foreach ($queryPart as $part) {
                    if (preg_match('/.+ as .+/', $part)) {
                        $parts = explode(' as ', $part);
                        $this->addColumn($parts[0], $parts[1]);
                    } else {
                        $this->addColumn($part, $part);
                    }
                }
            }
        }
    }

    /**
     * buildQuery
     *
     * generates the query builder
     *
     * @return QueryBuilder
     */
    public function buildQuery()
    {
        $qb = clone($this->queryBuilder);

        $qb = $this->addSearch($qb);
        $qb = $this->addGenericSearch($qb);
        $qb = $this->addOrder($qb);
        $qb = $this->addOffset($qb);
        $qb = $this->addLimits($qb);
        $this->debug('DataTables Query:' . $qb->getQuery()->getSQL() . ' ' . json_encode($qb->getParameters()));
        return $qb;
    }

    /**
     * addGenericSearch
     *
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function addGenericSearch(QueryBuilder $qb)
    {
        $search      = $this->requestParameters->getSearchString();
        $joinType    = 'or';
        $query       = '';
        $queryParams = array();

        // add search
        $this->debug("sSearch: {$search}");
        $searchColumns = $this->getColumns();

        if (!empty($searchColumns)) {
            $this->debug('SearchColumns:' . implode(', ',$searchColumns));
            foreach ($searchColumns as $name => $title) {
                if (strlen($search) > 0) {
                    $paramName = str_replace('.','_',$name) . '_search';
                    if (strlen($query) > 0) {
                        $query .= " {$joinType} ";
                    }
                    $query .= "{$name} LIKE :{$paramName}";
                    $queryParams[$paramName] = '%' . $search . '%';
                }
            }

            // add the parameters
            if (strlen($query) > 0) {
                $qb->andWhere($query);
                foreach ($queryParams as $name => $value) {
                    $qb->setParameter($name, $value);
                }
            }
        } else {
            $this->debug('No SearchColumns Found');
        }

        return $qb;
    }

    /**
     * addSearch
     *
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function addSearch(QueryBuilder $qb)
    {
        $searchCols  = $this->requestParameters->getSearchColumns();
        $query       = '';
        $joinType    = 'and';
        $queryParams = array();

        // build what the parameters are
        if (!empty($searchCols)) {
            foreach ($searchCols as $name => $value) {
                if (strlen($value) > 0) {
                    $paramName = str_replace('.','_',$name);
                    if (strlen($query) > 0) {
                        $query .= " {$joinType} ";
                    }
                    $query .= "{$name} LIKE :{$paramName}";
                    $queryParams[$paramName] = '%' . $value . '%';
                }
            }

            // add the parameters
            if (strlen($query) > 0) {
                $qb->andWhere($query);
                foreach ($queryParams as $name => $value) {
                    $qb->setParameter($name, $value);
                }
            }
        }

        return $qb;
    }

    /**
     * addOrder
     *
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    public function addOrder(QueryBuilder $qb)
    {
        $order = $this->requestParameters->getSortingColumns();
        if (!empty($order)) {
            foreach ($order as $sort=>$order) {
                if (strlen($sort) > 0) {
                    $qb->addOrderBy($sort, $order);
                }
            }
        }

        return $qb;
    }

    /**
     * addOffset
     *
     * @param QueryBuilder $qb
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function addOffset(QueryBuilder $qb)
    {
        $offset = $this->requestParameters->getOffset();
        if ($offset > 0) {
            $qb->setFirstResult($offset);
        }

        return $qb;
    }


    /**
     * addLimits
     *
     * @param QueryBuilder $qb
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function addLimits(QueryBuilder $qb)
    {
        $limit = $this->requestParameters->getDisplayLength();
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    /**
     * getTotalRecords
     *
     * gets the total records from the query
     *
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string                     $alias
     *
     * @return mixed
     */
    public function getTotalRecords(QueryBuilder $qb, $alias)
    {
        $qb = $this->addSearch($qb);
        $qb->select(array("count({$alias})"))
            ->setMaxResults(1);

        $rawResult = $qb->getQuery()->getArrayResult();

        return array_pop($rawResult[0]);
    }

    /**
     * setLogger
     *
     * @param $logger
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }
}