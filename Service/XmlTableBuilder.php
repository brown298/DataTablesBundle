<?php
namespace Brown298\DataTablesBundle\Service;

use Brown298\DataTablesBundle\MetaData\Column;
use Brown298\DataTablesBundle\MetaData\Format;
use Brown298\DataTablesBundle\MetaData\Table;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \SimpleXMLElement;
use Symfony\Component\Process\Exception\InvalidArgumentException;

/**
 * Class XmlTableBuilder
 * @package Brown298\DataTablesBundle\Service
 */
class XmlTableBuilder extends AbstractTableBuilder implements TableBuilderInterface
{

    /**
     * @var SimpleXMLElement
     */
    protected $rawTableConfig;

    /**
     * @var
     */
    protected $tableConfig;

    /**
     * @var string
     */
    protected $tableId;

    /**
     * @param ContainerInterface $container
     * @param EntityManager $em
     * @param $tableId
     * @param mixed $tableConfig
     */
    public function __construct(ContainerInterface $container, EntityManager $em, $tableId, SimpleXMLElement $tableConfig)
    {
        $this->container   = $container;
        $this->rawTableConfig = $tableConfig;
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
        $this->tableConfig = new Table();
        foreach($this->rawTableConfig->attributes() as $name => $attribute) {
            if (property_exists($this->tableConfig, $name)) {
                $this->tableConfig->$name = $attribute;
            }
        }

    }

    /**
     * @param SimpleXMLElement $value
     * @return Format
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     */
    protected function parseFormat(SimpleXMLElement $value)
    {
        $format = new Format();
        foreach($value->children() as $name=>$field) {
            if (property_exists($format, lcfirst($name))) {
                $name = lcfirst($name);
                if ($field->count() <=0) {
                    foreach($field->attributes() as $attributeName =>$fieldAttribute) {
                        $format->$name = $fieldAttribute->__toString();
                    }
                } else {

                    $format->$name = $this->parseDataFields($field);
                }
            }
        }

        return $format;
    }


    /**
     * @param SimpleXMLElement $field
     * @return array
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     */
    protected function parseDataFields(SimpleXMLElement $field)
    {
        $values = array();
        foreach($field->children() as $v) {
            $source = null;
            $name   = null;
            foreach($v->attributes() as $n => $a) {
                if ($n == 'source') {
                    $source = $a->__toString();
                }
                if ($n == 'name') {
                    $name = $a->__toString();
                }
            }

            if ($source == null || $name == null) {
                throw new InvalidArgumentException('DataTables requires a "source" and "name" attribute be provided for any Data provided to a formatter');
            }

            $values[$name] = $source;
        }
        return $values;
    }

    /**
     * buildMetaData
     *
     * build the columns and other metadata for this class
     */
    protected function buildMetaData()
    {
        $columnArray = array();

        foreach ($this->rawTableConfig->children() as $columns) {
            foreach ($columns as $columnData) {
                $column = new Column();
                foreach ($columnData->attributes() as $name => $attribute) {
                    if (property_exists($column, $name)) {
                        if (is_string($column->$name) || $column->$name == null) {
                            $column->$name = $attribute->__toString();
                        } else {
                            $column->$name = strtolower($attribute->__toString()) == 'true';
                        }
                    }
                }

                if ($columnData->count () > 0) {
                    foreach($columnData as $format) {
                        $column->format = $this->parseFormat($format);
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
        }

        $this->table->setColumns($columnArray);
        $this->table->setMetaData(
            array(
                'table'   => $this->tableConfig,
                'columns' => $this->columns,
            )
        );
    }

}