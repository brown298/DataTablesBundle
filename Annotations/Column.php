<?php
namespace Brown298\DataTablesBundle\Annotations;

/**
 * Class Column
 *
 * @package Brown298\DataTablesBundle\Annotations
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
     * @var mixed width valud for the column
     */
    public $width;
}