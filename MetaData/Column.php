<?php
namespace Brown298\DataTablesBundle\MetaData;

/**
 * Class Column
 *
 * @package Brown298\DataTablesBundle\MetaData
 * @author  John Brown <brown.john@gmail.com>
 *
 * @Annotation
 * @Target("PROPERTY")
 */
class Column
{
    /**
     * @var string dql source string
     */
    public $source;

    /**
     * @var string title for the column
     */
    public $name;

    /**
     * @var bool is the column visible
     */
    public $visible = true;

    /**
     * @var bool is the column sortable
     */
    public $sortable = true;

    /**
     * @var bool is the column searchable
     */
    public $searchable = true;

    /**
     * @var string css class to apply to column
     */
    public $class;

    /**
     * @var mixed width value for the column
     */
    public $width;

    /**
     * @var boolean
     */
    public $defaultSort = false;

    /**
     * @var
     */
    public $format = null;
}