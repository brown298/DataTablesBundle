<?php
namespace Brown298\DataTablesBundle\Service;


abstract class AbstractTableBuilder implements TableBuilderInterface
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $args;

    /**
     * @var mixed
     */
    protected $table;

    /**
     * @var mixed
     */
    protected $tableConfig;

    /**
     * @var string
     */
    protected $tableId;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;


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
        $this->buildMetaData();

        return $this->table;
    }

    /**
     * @param $table
     * @return string
     */
    protected function getClassName($table)
    {
        if (isset($table->class) && $table->class != null) {
            $className = $table->class;
        } else {
            $className = 'Brown298\DataTablesBundle\Test\DataTable\QueryBuilderDataTable';
        }
        return $className;
    }

    /**
     * parse
     *
     * implement parsing
     */
    protected function parse()
    {

    }

    /**
     * creates the table
     */
    protected function buildTable()
    {
        $this->parse();
        $className = $this->getClassName($this->tableConfig);

        array_shift($this->args);

        if(is_array($this->args) && !empty($this->args) ) {
            $ref         = new \ReflectionClass($className);
            $this->table = $ref->newInstanceArgs($this->args);
        } else {
            $this->table = new $className;
        }

        // pass the dependencies in, they can override them later if necessary
        $this->table->setContainer($this->container);
        $this->table->setEm($this->em);
        $this->table->hydrateObjects = true;
    }

    /**
     * buildMetaData
     *
     * build the columns and other metadata for this class
     */
    protected function buildMetaData()
    {

    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }


    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function setEm($em)
    {
        $this->em = $em;
    }

}