<?php
namespace Brown298\DataTablesBundle\Service;

use Brown298\DataTablesBundle\Exceptions\ProcessorException;
use Brown298\DataTablesBundle\Model\ResponseParameterBag;
use Brown298\DataTablesBundle\Service\AbstractServerProcessor;
use Brown298\DataTablesBundle\Service\Processor\ArrayProcessor;
use Brown298\DataTablesBundle\Service\Processor\ProcessorInterface;
use Brown298\DataTablesBundle\Service\Processor\QueryBuilderProcessor;
use Brown298\DataTablesBundle\Service\Processor\RepositoryProcessor;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ServerProcessService
 *
 * handles the server side processing
 *
 * @package Brown298\DataTablesBundle\Service
 * @author  John Brown <brown.john@gmail.com>
 */
class ServerProcessService extends AbstractServerProcessor
{

    /**
     * process
     *
     * gets the results
     *
     * @param null $dataFormatter
     * @param bool $getEntity
     *
     * @throws \Brown298\DataTablesBundle\Exceptions\ProcessorException
     * @return array
     */
    public function process($dataFormatter = null, $getEntity = false)
    {
        if (!($this->processor instanceof ProcessorInterface)) {
            Throw new ProcessorException("DataTables Processor not defined did you forget to set the data or add a query?");
        }

        $this->responseParameters = new ResponseParameterBag();
        $this->responseParameters->setRequest($this->requestParameters);

        $this->responseParameters = $this->processor->process($this->responseParameters, $getEntity);

        return $this->responseParameters->all($dataFormatter);
    }

    /*----------------------------------------Query Builder-----------------------------------------------------------*/
    /**
     * setQueryBuilder
     *
     * @param QueryBuilder $queryBuilder]
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->processor = new QueryBuilderProcessor($queryBuilder, $this->requestParameters, $this->logger);
    }

    /**
     * getQueryBuilder
     *
     * @return null
     */
    public function getQueryBuilder()
    {
        if ($this->processor == null || !($this->processor instanceof QueryBuilderProcessor)) {
            return null;
        }

        return $this->processor->getQueryBuilder();
    }

    /*----------------------------------------Array Processor---------------------------------------------------------*/
    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->processor = new ArrayProcessor($this->requestParameters, $this->logger);
        $this->processor->setData($data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if ($this->processor == null || !($this->processor instanceof ArrayProcessor)) {
            return null;
        }

        return $this->processor->getData();
    }

    /*----------------------------------------Repository Processor----------------------------------------------------*/

    /**
     * setRepository
     *
     * creates a repository based processor
     *
     * @param EntityRepository $repository
     */
    public function setRepository(EntityRepository $repository)
    {
        $this->processor = new RepositoryProcessor($repository, $this->requestParameters, $this->logger);
    }

    /**
     * getRepository
     *
     * @return Processor\Doctrine\ORM\EntityRepository|null
     */
    public function getRepository()
    {
        if ($this->processor == null || !($this->processor instanceof RepositoryProcessor)) {
            return null;
        }

        return $this->processor->getRepository();
    }

    /**
     * Adds support for magic finders.
     *
     * @param string $method
     * @param array $arguments
     *
     * @throws \Brown298\DataTablesBundle\Exceptions\ProcessorException
     * @return array|object The found entity/entities.
     *
     */
    public function __call($method, $arguments)
    {
        if ($this->processor == null || !($this->processor instanceof RepositoryProcessor)) {
            Throw new ProcessorException("Generic calls require a Repository Processor, create one by running setRepository");
        }

        return call_user_func_array(array($this->processor,$method), $arguments);
    }

    /**
     * findAll
     *
     * builds a findAll Query
     *
     * @throws \Brown298\DataTablesBundle\Exceptions\ProcessorException
     */
    public function findAll()
    {
        if ($this->processor == null || !($this->processor instanceof RepositoryProcessor)) {
            Throw new ProcessorException("FindAll requires a Repository Processor, create one by running setRepository");
        }

        $this->processor->buildFindAll();
    }

    /**
     * findBy
     *
     * builds a findBy query
     *
     * @param array $criteria
     * @param array $orderBy
     * @param null $limit
     * @param null $offset
     *
     * @throws \Brown298\DataTablesBundle\Exceptions\ProcessorException
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        if ($this->processor == null || !($this->processor instanceof RepositoryProcessor)) {
            Throw new ProcessorException("FindBy requires a Repository Processor, create one by running setRepository");
        }

        $this->processor->buildFindBy($criteria, $orderBy, $limit, $offset);
    }

}