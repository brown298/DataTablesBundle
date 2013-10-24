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
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->requestParameters = new RequestParameterBag();
        $this->requestParameters->fromRequest($request);
    }

    /**
     * @param QueryBuilder $queryBuilder]
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * process
     *
     * gets the results
     *
     * @return array
     */
    public function process()
    {
        $alias                    = $this->queryBuilder->getRootAlias();
        $this->responseParameters = new ResponseParameterBag();
        $qb                       = $this->buildQuery($alias);

        $this->responseParameters->setRequest($this->requestParameters);
        $this->responseParameters->setTotal($this->getTotalRecords(clone($this->queryBuilder), $alias));
        $this->responseParameters->setDisplayTotal($this->getTotalRecords($qb, $alias));
        $this->responseParameters->setData($qb->getQuery()->getArrayResults());

        return $this->responseParameters->all();
    }

    /**
     * buildQuery
     *
     * generates the query builder
     *
     * @param string $alias
     *
     * @return QueryBuilder
     */
    public function buildQuery($alias)
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
            $qb->andWhere("{$name} LIKE :{$name}")
                ->setParameter($name, '%' . $value . '%');
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
            $qb->addOrderBy($sort, $order);
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
        $qb->setFirstResult($this->requestParameters->getOffset());

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
        $qb->setMaxResults($this->requestParameters->getDisplayLength());

        return $qb;
    }

    /**
     * getTotalRecords
     *
     * gets the total records from the query
     *
     * @param string $alias
     * @return mixed
     */
    public function getTotalRecords(QueryBuilder $qb, $alias)
    {
        $qb->select("count({$alias}.id) as count")
            ->setMaxResults(1);

        $rawResult = $qb->getQuery()->getArrayResults();
        return $rawResult[0]['count'];
    }
}