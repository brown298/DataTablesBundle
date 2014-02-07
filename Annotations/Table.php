<?php
namespace Brown298\DataTablesBundle\Annotations;

/**
 * Class Table
 *
 * @package Brown298\DataTablesBundle\Annotations
 * @author  John Brown <brown.john@gmail.com>
 *
 * @Annotation
 * @Target("CLASS")
 */
class Table
{
    /**
     * @var string id for the data table
     */
    public $id;

    /**
     * @var bool should loading be deferred
     */
    public $deferLoading = false;

    /**
     * @var integer number of records to display
     */
    public $displayLength = 20;

    /**
     * @var bool should the table be paginated
     */
    public $paginate = true;

    /**
     * @var bool is the table sortable
     */
    public $sortable = true;

    /**
     * @var bool is the table searchable
     */
    public $searchable = true;

    /**
     * @var bool
     */
    public $serverSideProcessing = false;

    /***
     * @var bool
     */
    public $processing = true;

    /**
     * @var bool
     */
    public $info = true;

    /**
     * @var string
     */
    public $paginationType = 'full_numbers';

    /**
     * @var bool
     */
    public $changeLength = true;

    /**
     * @var string
     */
    public $class = '';
}