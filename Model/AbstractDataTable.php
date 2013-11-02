<?php
namespace Brown298\DataTablesBundle\Model;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractDataTable
 *
 * @package Brown298\DataTablesBundle\Model
 * @author  John Brown <brown.john@gmail.com>
 */
abstract class AbstractDataTable implements DataTableInterface, ContainerAwareInterface
{
    /**
     * @var array defintion of the column as DQLName => display
     */
    protected $columns = array();

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em = null;

    /**
     * @var null
     */
    protected $dataFormatter = null;

    /**
     * @var null
     */
    protected $container = null;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder = null;

    /**
     * __construct
     *
     * @param EntityManager $em
     * @param array $columns
     */
    public function __construct(EntityManager $em = null, array $columns = null)
    {
        $this->em      = $em;
        if ($columns !== null) {
            $this->columns = $columns;
        }
    }

    /**
     * getColumns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * setColumns
     *
     * @param array $columns
     * @return null|void
     */
    public function setColumns(array $columns = null)
    {
        $this->columns = $columns;
    }

    /**
     * getDataByQueryBuilder
     *
     * uses a query builder to get the required data
     *
     * @param Request      $request
     * @param QueryBuilder $qb
     * @param null         $dataFormatter
     *
     * @return JsonResponse
     */
    protected function getDataByQueryBuilder(Request $request, QueryBuilder $qb, $dataFormatter = null)
    {
        $service = $this->container->get('data_tables.service');

        // logger is optional
        if ($this->container->has('logger')) {
            $logger  = $this->container->get('logger');
            $service->setLogger($logger);
        }

        if ($service->getRequest() == null) {
            $service->setRequest($request);
        }

        $service->setQueryBuilder($qb);
        if ($service->getColumns() == null) {
            $service->setColumns($this->columns);
        }

        return $this->execute($service, $dataFormatter);
    }

    /**
     * execute
     *
     * @param $service
     * @param $formatter
     */
    public function execute($service, $formatter)
    {
        return $service->process($formatter, false);
    }

    /**
     * getData
     *
     * override this function to return a raw data array
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function getData(Request $request)
    {
        return array();
    }

    /**
     * getQueryBuilder
     *
     * override this function to return a query builder
     *
     * @param Request $request
     *
     * @return QueryBuilder|null
     */
    public function getQueryBuilder(Request $request)
    {
        return $this->queryBuilder;
    }

    /**
     * getJsonResponse
     *
     * @param Request $request
     *
     * @param callable|null $dataFormatter
     *
     * @return JsonResponse
     */
    public function getJsonResponse(Request $request, \Closure $dataFormatter = null)
    {
        $qb = ($this->queryBuilder !== null) ? $this->queryBuilder : $this->getQueryBuilder($request);

        if ($qb !== null) {
            $data = $this->getDataByQueryBuilder($request, $qb, $dataFormatter);
        } else {
            $data = $this->getData($request, $dataFormatter);
        }

        return new JsonResponse($data);
    }

    /**
     * isAjaxRequest
     *
     * @param Request $request
     * @return bool
     */
    public function isAjaxRequest(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function processRequest(Request $request, \Closure $dataFormatter = null)
    {
        if (!$this->isAjaxRequest($request)) {
            return false;
        }

        if ($dataFormatter !== null) {
            $dataFormatter = $this->dataFormatter;
        } elseif ($this->getDataFormatter() !== null) {
            $dataFormatter = $this->getDataFormatter();
        }

        // ensure at least a minimal formatter is used
        if ($dataFormatter === null) {
            $dataFormatter = function($data) { return $data; };
        }

        return $this->getJsonResponse($request, $dataFormatter);
    }

    /**
     * @param callable|null $dataFormatter
     * @return mixed|void
     */
    public function setDataFormatter(\Closure $dataFormatter = null)
    {
        $this->dataFormatter = $dataFormatter;
    }

    /**
     * @return null
     */
    public function getDataFormatter()
    {
        return $this->dataFormatter;
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @return mixed|void
     */
    public function setEm(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param QueryBuilder $qb
     */
    public function setQueryBuilder(QueryBuilder $qb)
    {
        $this->queryBuilder = $qb;
    }
}