<?php
namespace Brown298\DataTablesBundle\Model\DataTable;

use Brown298\DataTablesBundle\Exceptions\ProcessorException;
use Brown298\DataTablesBundle\Service\ServerProcessService;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AbstractRepositoryDataTable
 *
 * @package Brown298\DataTablesBundle\Model\DataTable
 * @author  John Brown <brown.john@gmail.com>
 */
class AbstractRepositoryDataTable extends AbstractQueryBuilderDataTable implements RepositoryDataTableInterface
{
    /**
     * @var Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * @var Brown298\DataTablesBundle\Service\ServerProcessService
     */
    protected $serverProcessorService;

    /**
     * findAll
     *
     * @throws \Brown298\DataTablesBundle\Exceptions\ProcessorException
     */
    public function findAll()
    {
        if (!($this->serverProcessorService instanceof ServerProcessService)) {
           throw new ProcessorException('The container must be set');
        }

        if (!($this->repository instanceOf EntityRepository)) {
            throw new ProcessorException('The entity repository must be set');
        }

        $this->serverProcessorService->setRepository($this->repository);

        return $this->serverProcessorService->findAll();
    }

    /**
     * findBy
     *
     * @param array $criteria
     * @param array $orderBy
     * @param null  $limit
     * @param null  $offset
     *
     * @throws \Brown298\DataTablesBundle\Exceptions\ProcessorException
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        if (!($this->serverProcessorService instanceof ServerProcessService)) {
            throw new ProcessorException('The container must be set');
        }

        if (!($this->repository instanceOf EntityRepository)) {
            throw new ProcessorException('The entity repository must be set');
        }

        $this->serverProcessorService->setRepository($this->repository);

        return $this->serverProcessorService->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * __call
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     * @throws \Brown298\DataTablesBundle\Exceptions\ProcessorException
     */
    public function __call($method, $arguments)
    {
        if (!($this->serverProcessorService instanceof ServerProcessService)) {
            throw new ProcessorException('The container must be set');
        }

        if (!($this->repository instanceOf EntityRepository)) {
            throw new ProcessorException('The entity repository must be set');
        }

        $this->serverProcessorService->setRepository($this->repository);

        return call_user_func_array(array($this->serverProcessorService,$method), $arguments);
    }

    /**
     * setContainer
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);
        if ($container !== null) {
            $this->serverProcessorService = $container->get('data_tables.service');
        }
    }

    /**
     * @param \Brown298\DataTablesBundle\Model\DataTable\Doctrine\ORM\EntityRepository $repository
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return \Brown298\DataTablesBundle\Model\DataTable\Doctrine\ORM\EntityRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }
} 