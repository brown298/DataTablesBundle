<?php
namespace Brown298\DataTablesBundle\Service;


use Brown298\DataTablesBundle\MetaData\Column;
use Brown298\DataTablesBundle\MetaData\Format;
use Brown298\DataTablesBundle\MetaData\Table;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Exception\InvalidArgumentException;
use Symfony\Component\Yaml\Parser;

/**
 * Class YmlTableBuilder
 * @package Brown298\DataTablesBundle\Service
 */
class YmlTableBuilder extends AbstractTableBuilder implements TableBuilderInterface
{

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
     *
     * build the columns and other metadata for this class
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

            if (!isset($column->source)) {
                throw new InvalidArgumentException('DataTables requires a "source" attribute be provided for a column');
            }

            if (!isset($column->name)) {
                throw new InvalidArgumentException('DataTables requires a "name" attribute be provided for a column');
            }

            $this->columns[]  = $column;
            $columnArray[$column->source] = $column->name;
        }

        $this->table->setColumns($columnArray);
        $this->table->setMetaData(
            array(
                'table'   => $this->tableAnnotations,
                'columns' => $this->columns,
            )
        );
    }


}