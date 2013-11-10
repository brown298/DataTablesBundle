<?php
namespace Brown298\DataTablesBundle\Service\Processor;

use Brown298\DataTablesBundle\Model\RequestParameterBag;
use Brown298\DataTablesBundle\Model\ResponseParameterBag;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;

/**
 * Class RepositoryProcessor
 *
 *
 * @package Brown298\DataTablesBundle\Service\Processor
 * @author  John Brown <brown.john@gmail.com>
 */
class RepositoryProcessor extends QueryBuilderProcessor implements ProcessorInterface
{
    /**
     * @var Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * @var array
     */
    protected $criteria;

    /**
     * @var array|null
     */
    protected $orderBy;

    /**
     * @var int|null
     */
    protected $limit;

    /**
     * @var int|null;
     */
    protected $offset;

    /**
     * __construct
     *
     * @param EntityRepository $repository
     * @param RequestParameterBag $requestParameters
     * @param LoggerInterface $logger
     */
    public function __construct(EntityRepository $repository, RequestParameterBag $requestParameters, LoggerInterface $logger )
    {
        $this->setRepository($repository);
        parent::__construct($this->queryBuilder, $requestParameters, $logger);
    }

    /**
     * Adds support for magic finders.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return array|object The found entity/entities.
     *
     * @throws ORMException
     * @throws \BadMethodCallException If the method called is an invalid find* method
     *                                 or no find* method at all and therefore an invalid
     *                                 method call.
     */
    public function __call($method, $arguments)
    {
        if (!method_exists($this->repository, $method)) {
            throw new \BadMethodCallException("Method {$method} does not exist on repository");
        }

        $qb = call_user_func_array(array($this->repository,$method), $arguments);

        if ((!$qb instanceof QueryBuilder)) {
            throw new \BadMethodCallException("Method {$method} must return a QueryBuilder object");
        }

        $this->setQueryBuilder($qb);
        return $qb;
    }

    /**
     * buildFindAll
     *
     * creates a findAll search
     *
     * @return $this
     */
    public function buildFindAll()
    {
        return $this->buildFindBy(array());
    }

    /**
     * buildFindBy
     *
     * adds the findBy criteria
     *
     * @param array $criteria
     * @param array $orderBy
     * @param null $limit
     * @param null $offset
     *
     * @return $this
     */
    public function buildFindBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $this->setCriteria($criteria);
        $this->setOrderBy($orderBy);
        $this->setLimit($limit);
        $this->setOffset($offset);

        return $this;
    }

    /**
     * tells the processor to run
     *
     * @param ResponseParameterBag $responseParameters
     * @param bool $getEntity
     *
     * @return mixed
     */
    public function process(ResponseParameterBag $responseParameters = null, $getEntity = false)
    {
        // add the offset
        if ($this->offset != null) {
            $this->requestParameters->setOffset($this->getOffset());
        }

        // add the limit
        if ($this->limit != null) {
            $this->requestParameters->setDisplayLength($this->limit);
        }

        // add ordering;
        $this->addOrdering($this->orderBy);

        // add criteria
        $this->addCriteria($this->criteria);

        return parent::process($responseParameters, $getEntity);
    }

    /**
     * addOrdering
     *
     * @param array $orderBy
     */
    protected function addOrdering(array $orderBy = null)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $col => $dir) {
                $this->queryBuilder->addOrderBy($col, $dir);
            }
        }
    }


    /**
     * addCriteria
     *
     * adds the findBy criteria
     *
     * @param array $criteria
     */
    protected function addCriteria(array $criteria = null)
    {
        if (!empty($criteria)) {
            foreach ($criteria as $name => $value) {
                $paramName = str_replace('.','_',$name);
                $this->queryBuilder->andWhere("{$name} = :{$paramName}")
                    ->setParameter($paramName, $value);
            }
        }
    }

    /**
     * setRepository
     *
     * @param EntityRepository $repo
     */
    public function setRepository(EntityRepository $repo)
    {
        $this->queryBuilder = $repo->createQueryBuilder('dt');
        $this->repository   = $repo;
    }

    /**
     * getRepository
     *
     * @return Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param array $criteria
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * @return array
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param int|null $limit
     */
    public function setLimit($limit = null)
    {
        $this->limit = $limit;
    }

    /**
     * @return int|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int|null $offset
     */
    public function setOffset($offset = null)
    {
        $this->offset = $offset;
    }

    /**
     * @return int|null
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param array|null $orderBy
     */
    public function setOrderBy($orderBy = null)
    {
        $this->orderBy = $orderBy;
    }

    /**
     * @return array|null
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }



}