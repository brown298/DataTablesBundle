<?php
namespace Brown298\DataTablesBundle\Service;

use Brown298\DataTablesBundle\Annotations\Table;
use Brown298\DataTablesBundle\Model\DataTable\QueryBuilderDataTableInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TableBuilder
 * @package Brown298\DataTablesBundle\Service
 * @author  John Brown <brown.john@gmail.com>
 */
class TableBuilder
{
    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    private $reader;

    /**
     * @var Table
     */
    protected $tableAnnotations;

    /**
     * @var array
     */
    protected $args;

    /**
     * @var mixed
     */
    protected $table;

    /**
     * @param ContainerInterface $container
     * @param AnnotationReader $reader
     * @param Table $table
     */
    public function __construct(ContainerInterface $container, AnnotationReader $reader, Table $table)
    {
        $this->container         = $container;
        $this->reader            = $reader;
        $this->tableAnnotations  = $table;
    }

    protected function buildMeta()
    {
        /** @todo add metadata to object */
    }

    protected function buildColumns()
    {
        /** @todo add crate table column definition */
    }

    /**
     * creates the table
     */
    protected function buildTable()
    {
        $className = $this->tableAnnotations->class;
        array_shift($this->args);

        if(!empty($this->args)) {
            $ref         = new \ReflectionClass($className);
            $this->table = $ref->newInstanceArgs($this->args);
        } else {
            $this->table = new $className;
        }

        // pass the dependencies in, they can override them later if necessary
        $this->table->setContainer($this->container);
    }

    /**
     * build
     *
     * @param array $args
     * @return mixed
     */
    public function build(array $args = array())
    {
        $this->args = $args;
        $this->buildTable();
        $this->buildMeta();
        $this->buildColumns();

        return $this->table;
    }
}