<?php
namespace Brown298\DataTablesBundle\Service;

use Brown298\DataTablesBundle\Model\RequestParameterBag;
use Brown298\DataTablesBundle\Model\ResponseParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ServerProcessService
 *
 * handles the server side processing
 *
 * @package Brown298\DataTablesBundle\Service
 * @author  John Brown <john.brown@partnerweekly.com>
 */
class ServerProcessService
{
    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Brown298\DataTablesBundle\Model\RequestParameterBag
     */
    protected $requestParameters;

    /**
     * @var \Brown298\DataTablesBundle\Model\ResponseParameterBag
     */
    protected $responseParameters;

    /**
     * @var array
     */
    protected $data = null;

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->requestParameters = new RequestParameterBag();
        $this->requestParameters->fromRequest($request);
    }

    /**
     * getRequest
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
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
     * parseColumns
     *
     * builds an array of the columns
     *
     * @param QueryBuilder $qb
     */
    public function parseColumns(QueryBuilder $qb)
    {
        $selects = $qb->getDQLPart('select');

        foreach ($selects as $queryPart) {
            foreach ($queryPart as $part) {
                if (preg_match('/.+ as .+/', $part)) {
                    $parts = explode(' as ', $part);
                    $this->requestParameters->addColumn($parts[0], $parts[1]);
                } else {
                    $this->requestParameters->addColumn($part, $part);
                }
            }
        }
    }

    /**
     * addColumn
     *
     * @param $name
     * @param $title
     */
    public function addColumn($name, $title)
    {
        $this->requestParameters->addColumn($name, $title);
    }

    /**
     * setColumns
     *
     * @param array $columns
     */
    public function setColumns(array $columns)
    {
        $this->requestParameters->setColumns($columns);
    }

    /**
     * getColumns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->requestParameters->getColumns();
    }

    /**
     * process
     *
     * gets the results
     *
     * @param null $dataFormatter
     *
     * @return array
     */
    public function process($dataFormatter = null)
    {
        $this->responseParameters = new ResponseParameterBag();
        $this->responseParameters->setRequest($this->requestParameters);
        if ($this->data == null) {
            $qb      = $this->buildQuery();
            $aliases = $qb->getRootAliases();
            $alias   = $aliases[0];

            $this->responseParameters->setData($qb->getQuery()->getArrayResult());
            $total        = $this->getTotalRecords(clone($this->queryBuilder), $alias);
            $displayTotal = $this->getTotalRecords(clone($this->queryBuilder), $alias);
            
            $this->responseParameters->setTotal($total);
            $this->responseParameters->setDisplayTotal($displayTotal);
        } else {
            $offset = $this->requestParameters->getOffset();
            $length = $this->requestParameters->getDisplayLength();

            if ($length > 0) {
                $data = array_slice($this->data, $offset, $length);
            } else {
                $data = array_slice($this->data, $offset);
            }

            $this->responseParameters->setData($data);
            $this->responseParameters->setTotal(count($this->data));
            $this->responseParameters->setDisplayTotal(count($data));
        }

        return $this->responseParameters->all($dataFormatter);
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
        $qb = $this->addOrder($qb);
        $qb = $this->addOffset($qb);
        $qb = $this->addLimits($qb);

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
        $search = $this->requestParameters->getSearchColumns();

        foreach ($search as $name => $value) {
            $paramName = str_replace('.','_',$name);
            $qb->andWhere("{$name} LIKE :{$paramName}")
                ->setParameter($paramName, '%' . $value . '%');
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

        foreach ($order as $sort=>$order) {
            if (strlen($sort) > 0) {
                $qb->addOrderBy($sort, $order);
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
            $qb->setFirstResult($this->requestParameters->getOffset());
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
            $qb->setMaxResults($this->requestParameters->getDisplayLength());
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
        $qb->select(array("count({$alias}.id)"))
            ->setMaxResults(1);

        $rawResult = $qb->getQuery()->getArrayResult();
        return array_pop($rawResult[0]);
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return \Brown298\DataTablesBundle\Model\RequestParameterBag
     */
    public function getRequestParameters()
    {
        return $this->requestParameters;
    }

    /**
     * @return \Brown298\DataTablesBundle\Model\ResponseParameterBag
     */
    public function getResponseParameters()
    {
        return $this->responseParameters;
    }
}