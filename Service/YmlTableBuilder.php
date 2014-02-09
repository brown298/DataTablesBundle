<?php
namespace Brown298\DataTablesBundle\Service;


use Brown298\DataTablesBundle\MetaData\Column;
use Brown298\DataTablesBundle\MetaData\Format;
use Brown298\DataTablesBundle\MetaData\Table;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Parser;

/**
 * Class YmlTableBuilder
 * @package Brown298\DataTablesBundle\Service
 */
class YmlTableBuilder implements TableBuilderInterface
{

    /**
     * @var array
     */
    protected $args;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var array
     */
    protected $tableConfig;

    /**
     * @var string
     */
    protected $tableId;

    /**
     * @var QueryBuilderDataTable
     */
    protected $table;

    /**
     * @var array
     */
    protected $columns = array();

    /**
     * @var Table
     */
    protected $tableAnnotations;

    /**
     * @param ContainerInterface $container
     * @param EntityManager $em
     * @param string $tableId
     * @param array $tableConfig
     */
    public function __construct(ContainerInterface $container, EntityManager $em, $tableId, array $tableConfig)
    {
        $this->container   = $container;
        $this->tableConfig = $tableConfig;
        $this->em          = $em;
        $this->tableId     = $tableId;
    }

    /**
     * parse
     *
     * build the metadata
     */
    protected function parse()
    {
        $this->tableAnnotations     = new Table();
        $this->tableAnnotations->id = $this->tableId;
        foreach($this->tableConfig as $name => $data) {
            if ($name != 'columns') {
                if (property_exists($this->tableAnnotations, $name)) {
                    $this->tableAnnotations->$name = $data;
                }
            }
        }
    }

    /**
     * parseFormat
     *
     * @param array $value
     * @return Format
     * @throws \RuntimeException
     */
    protected function parseFormat(array $value)
    {
        if (!isset($value['dataFields'])) {
            throw new \RuntimeException('Format requires that dataFields be set');
        }

        $format = new Format();
        $format->dataFields = $value['dataFields'];
        if (isset($value['template'])) {
            $format->template = $value['template'];
        }

        return $format;
    }

    /**
     * buildMetaData
     */
    protected function buildMetaData()
    {
        $columnArray = array();
        $columnData  = $this->tableConfig['columns'];

        foreach ($columnData as $id => $properties) {
            $column = new Column();
            $column->source = $id;
            foreach($properties as $name => $value) {
                if (property_exists($column, $name)) {
                    if ($name != 'format') {
                        $column->$name = $value;
                    } else {
                        $column->format = $this->parseFormat($value);
                    }
                }
            }

            $this->columns[]  = $column;
            $columnArray[$id] = $column->name;
        }

        $this->table->setColumns($columnArray);
        $this->table->setMetaData(
            array(
                'table'   => $this->tableAnnotations,
                'columns' => $this->columns,
            )
        );
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
     * creates the table
     */
    protected function buildTable()
    {
        $this->parse();
        $className = $this->getClassName($this->tableConfig);

        array_shift($this->args);

        if(!empty($this->args)) {
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
}