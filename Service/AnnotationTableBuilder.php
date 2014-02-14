<?php
namespace Brown298\DataTablesBundle\Service;

use Brown298\DataTablesBundle\MetaData\Table;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Exception\InvalidArgumentException;

/**
 * Class TableBuilder
 * @package Brown298\DataTablesBundle\Service
 * @author  John Brown <brown.john@gmail.com>
 */
class AnnotationTableBuilder extends AbstractTableBuilder implements TableBuilderInterface
{
    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    private $reader;

    /**
     * @var array
     */
    protected $columns = array();

    /**
     * @var string
     */
    protected $columnNs = 'Brown298\DataTablesBundle\MetaData\Column';

    /**
     * @var string
     */
    protected $formatNs = 'Brown298\DataTablesBundle\MetaData\Format';

    /**
     * @var string
     */
    protected $defaultSortNs = 'Brown298\DataTablesBundle\MetaData\DefaultSort';

    /**
     * @param ContainerInterface $container
     * @param EntityManager $em
     * @param AnnotationReader $reader
     * @param Table $table\
     */
    public function __construct(ContainerInterface $container, EntityManager $em, AnnotationReader $reader, Table $table)
    {
        $this->container   = $container;
        $this->reader      = $reader;
        $this->tableConfig = $table;
        $this->em          = $em;
        $this->tableId     = $table->id;
    }

    /**
     * buildMetaData
     *
     * build the columns and other metadata for this class
     */
    protected function buildMetaData()
    {
        $columnArray = array();
        $className   = $this->getClassName($this->tableConfig);
        $refl        = new \ReflectionClass($className);
        $properties  = $refl->getProperties();

        foreach ($properties as $property) {
            $column = $this->reader->getPropertyAnnotation($property, $this->columnNs);

            if (!empty($column)) {
                if (!isset($column->source)) {
                    throw new InvalidArgumentException('DataTables requires a "source" attribute be provided for a column');
                }

                if (!isset($column->name)) {
                    throw new InvalidArgumentException('DataTables requires a "name" attribute be provided for a column');
                }

                // check for default
                $default = $this->reader->getPropertyAnnotation($property, $this->defaultSortNs);
                if (!empty($default)) {
                    $column->defaultSort = true;
                }

                // check for formatting
                $format = $this->reader->getPropertyAnnotation($property, $this->formatNs);
                if (!empty($format)) {
                    if (!isset($format->dataFields)) {
                        throw new InvalidArgumentException('DataTables requires a "dataFields" attribute be provided for a column formatter');
                    }
                    $column->format = $format;
                }

                $this->columns[] = $column;
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