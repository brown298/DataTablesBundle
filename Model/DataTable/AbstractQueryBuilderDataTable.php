<?php
namespace Brown298\DataTablesBundle\Model\DataTable;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractQueryBuilderDataTable
 *
 * @package Brown298\DataTablesBundle\Model\DataTable
 * @author  John Brown <brown.john@gmail.com>
 */
abstract class AbstractQueryBuilderDataTable extends AbstractDataTable implements DataTableInterface
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder = null;

    /**
     * @var bool
     */
    public $hydrateObjects = false;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * getData
     *
     * override this function to return a raw data array
     *
     * @param Request $request
     * @param null    $dataFormatter
     *
     * @return JsonResponse
     */
    public function getData(Request $request, $dataFormatter = null)
    {
        $this->queryBuilder = $this->getQueryBuilder($request);

        return $this->getDataByQueryBuilder($request, $this->queryBuilder, $dataFormatter);
    }

    /**
     * getObject Value
     *
     * allows for relations based on things like faq.createdBy.id
     *
     * @param $row
     * @param $source
     * @return string
     */
    protected function getObjectValue($row, $source)
    {
        /** @todo use querybuilder to determine values */
        return parent::getObjectValue($row, $source);
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
        if ($service->getColumns() == null || count($service->getColumns()) != count($this->columns)) {
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
        return $service->process($formatter, $this->hydrateObjects);
    }

    /**
     * setQueryBuilder
     *
     * @param QueryBuilder $qb
     */
    public function setQueryBuilder(QueryBuilder $qb)
    {
        $this->queryBuilder = $qb;
    }

    /**
     * getQueryBuilder
     *
     * override this function to return a query builder
     *
     * @param Request $request
     * @throws \RuntimeException
     * @return QueryBuilder|null
     */
    public function getQueryBuilder(Request $request = null)
    {
        if ($this->queryBuilder != null) {
            return $this->queryBuilder;
        }

        // use metadata if possbile
        if (is_array($this->metaData) && isset($this->metaData['table']) && $this->metaData['table']->entity != null) {
            if ($this->em == null) {
                throw new \RuntimeException('You must provide an entity manger to use the DataTables Entity');
            }

            // generate via metadata if possible
            $repo = $this->em->getRepository($this->metaData['table']->entity);

            if ($this->metaData['table']->queryBuilder != null) {
                $function = $this->metaData['table']->queryBuilder;

                if(method_exists($repo, $function)) {
                    return $repo->$function();
                }
            } else {
                $tokens = explode('\\', $this->metaData['table']->entity);
                $alias  = array_pop($tokens);
                return $repo->createQueryBuilder($alias);
            }
        }

        throw new \RuntimeException('Could not generate query builder for data table');
    }


    /**
     * @param EntityManager $em
     */
    public function setEm(EntityManager $em = null)
    {
        $this->em = $em;
    }

    /**
     * @return \Brown298\DtTestBundle\Model\Doctrine\ORM\EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }
} 