<?php
namespace Brown298\DataTablesBundle\Model;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestParameterBag
 * @package Brown298\DataTablesBundle\Model
 * @author  John Brown <brown.john@gmail.com>
 */
class RequestParameterBag extends AbstractParamterBag
{

    /**
     * @var array
     */
    public $parameterNames = array(
        'offset' => array(
            'name'    => 'Offset',
            'const'   => 'iDisplayStart',
            'default' => 0,
        ),  // current offset
        'length' => array(
            'name'    => 'Display Length',
            'const'   => 'iDisplayLength',
            'default' => -1,
        ), // count on current page
        'sortLength' => array(
            'name'    => 'Sorting Count',
            'const'   => 'iSortingCols',
            'default' => 0,
        ), // length of sorting column data
        'sortCols' => array(
            'name'    => 'Sorting Columns',
            'const'   => 'iSortCol_%d',
            'default' => null,
        ), // sorting column definitions
        'sortDir' => array(
            'name'    => 'Sorting Directions',
            'const'   => 'sSortDir_%d',
            'default' => 'asc',
        ), // direction to sort
        'sortable' => array(
            'name'    => 'Sortable Columns',
            'const'   => 'bSortable_%d',
            'default' => 'false',
        ), // columns that are sortable
        'echo' => array(
            'name'    => 'Echo',
            'const'   => 'sEcho',
            'default' => null,
        ), //
        'search' => array(
            'name'    => 'Search String',
            'const'   => 'sSearch',
            'default' => '',
        ), // string to search for
        'searchable' => array(
            'name'    => 'Searchable Columns',
            'const'   => 'bSearchable_%d',
            'default' => 'false',
        ), // determine if the columns are searchable
        'searchCols' => array(
            'name'    => 'Search Columns',
            'const'   => 'sSearch_%d',
            'default' => null,
        ), // individual column search
    );

    /**
     * @var array column data
     */
    protected $columns = array();

    /**
     * fromRequest
     *
     * sets the data from the request
     *
     * @param Request $request
     */
    public function fromRequest(Request $request)
    {
        switch ($request->getMethod()) {
            case 'GET':
                $this->parameters = $request->query->all();
                break;
            case 'POST':
                $this->parameters = $request->request->all();
                break;
        }
    }

    /**
     * @return mixed
     */
    public function getEcho()
    {
        return $this->getVarByName('echo');
    }

    /**
     * addColumn
     */
    public function addColumn($name, $entity)
    {
        $this->columns[$name] = $entity;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * setColumns
     *
     * @param array $columns
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * getSearchString
     *
     * @return null
     */
    public function getSearchString()
    {
        return $this->get(
            $this->parameterNames['search']['const'],
            $this->parameterNames['search']['default']
        );
    }

    /**
     * getSearchColumns
     *
     * @param array
     *
     * @return array
     */
    public function getSearchColumns()
    {
        $results = array();
        $i       = 0;

        foreach ($this->columns as $name => $entity) {
            if ($this->isSearchable($i)) {
                if ($this->hasColumnSearch($i)) {
                    $results[$name] = $this->getColumnSearch($i);
                } else {
                    $results[$name] = $this->getSearchString();
                }
            }

            $i += 1;
        }
        return $results;
    }

    /**
     * isSearchable
     *
     * @param integer$i
     *
     * @return bool
     */
    public function isSearchable($i)
    {
        return $this->getVarByName('searchable', $i) == 'true';
    }

    /**
     * hasColumnSearch
     *
     * @param integer $i
     *
     * @return bool
     */
    public function hasColumnSearch($i)
    {
        return $this->getVarByName('searchCols', $i) !== null;
    }

    /**
     * getColumnSearch
     *
     * @param integer $i
     *
     * @return mixed
     */
    public function getColumnSearch($i)
    {
        return $this->getVarByName('searchCols', $i);
    }

    /**
     * getSortingColumns
     *
     * gets an array of the sorting columns and their directions
     *
     * @return array
     */
    public function getSortingColumns()
    {
        $result = array();
        $length = $this->getSortingLength();
        for ($i=0; $i < $length; $i++) {
            if ($this->isColumnSortable($i)) {
                $result[$this->getSortingColumn($i)] = $this->getSortingDirection($i);
            }
        }

        return $result;
    }

    /**
     * getSortingDirection
     *
     * @param integer $id
     *
     * @return mixed
     */
    public function getSortingDirection($id)
    {
        return $this->getVarByName('sortDir', $id);
    }

    /**
     * getSortingColumn
     *
     * gets a sorting column by id
     *
     * @param integer $id
     *
     * @return string|null
     */
    public function getSortingColumn($id)
    {
        return $this->getVarByName('sortCols', $id);
    }

    /**
     * isColumnSortable
     *
     * determines if a column is sortable by id
     *
     * @param integer $id
     *
     * @return bool
     */
    public function isColumnSortable($id)
    {
        return ($this->getVarByName('sortLength', $id) == 'true');
    }

    /**
     * getSortingLength
     *
     * gets the count of elements being sorted on
     *
     * @return int
     */
    public function getSortingLength()
    {
        return intval($this->getVarByName('sortLength'));
    }

    /**
     * getOffset
     *
     * gets the offset from the start of the pages
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->getVarByName('offset');
    }

    /**
     * getDisplayLength
     *
     * gets number of items to show on a page
     *
     * @return integer
     */
    public function getDisplayLength()
    {
        return $this->getVarByName('length');
    }

    /**
     * getCurrentPage
     *
     * calculates the current page
     *
     * @return integer
     */
    public function getCurrentPage()
    {
        $offset = $this->getOffset();
        $length = $this->getDisplayLength();

        return floor($offset/$length);
    }

}