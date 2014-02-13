<?php
namespace Brown298\DataTablesBundle\MetaData;

/**
 * Class Table
 *
 * @package Brown298\DataTablesBundle\MetaData
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
     * @var bool use server side processing?
     */
    public $serverSideProcessing = false;

    /***
     * @var bool show the processing text
     */
    public $processing = true;

    /**
     * @var bool display data tables info text
     */
    public $info = true;

    /**
     * @var string paginator type
     */
    public $paginationType = 'full_numbers';

    /**
     * @var bool allow changing of display length
     */
    public $changeLength = true;

    /**
     * @var string
     */
    public $class = '';

    /**
     * @var null|string name of entity to call repsitory for
     */
    public $entity = null;

    /**
     * @var null|string repository function to call to get query builder
     */
    public $queryBuilder = null;
}