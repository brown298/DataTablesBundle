<?php
namespace Brown298\DataTablesBundle\Tests\Model;

use Brown298\DataTablesBundle\Model\RequestParameterBag;
use Phake;
use Brown298\DataTablesBundle\Test\AbstractBaseTest;

/**
 * Class RequestParameterBagTest
 *
 * @package Brown298\DataTablesBundle\Tests\Model
 * @author  John Brown <brown.john@gmail.com>
 */
class RequestParameterBagTest extends AbstractBaseTest
{

    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var Brown298\DataTablesBundle\Model\RequestParameterBag
     */
    protected $model;

    /**
     * @var Symfony\Component\HttpFoundation\ParameterBag
     */
    protected $parameterBag;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();

        $this->request      = Phake::mock('Symfony\Component\HttpFoundation\Request');
        $this->parameterBag = Phake::mock('Symfony\Component\HttpFoundation\ParameterBag');
        $this->model        = new RequestParameterBag();
    }

    /**
     * testGetSetColumnsEmpty
     */
    public function testGetSetColumnsEmpty()
    {
        $this->model->setColumns(array());
        $this->assertEquals(array(), $this->model->getColumns());
    }

    /**
     * testFromRequestGet
     */
    public function testFromRequestGet()
    {
        $expectedResults = array('tst'=>'123');
        Phake::when($this->request)->getMethod()->thenReturn('GET');
        Phake::when($this->parameterBag)->all()->thenReturn($expectedResults);
        $this->request->query = $this->parameterBag;

        $this->model->fromRequest($this->request);

        $this->assertEquals($expectedResults, $this->getProtectedValue($this->model, 'parameters'));
        Phake::verify($this->parameterBag)->all();
    }

    /**
     * testFromRequestPost
     */
    public function testFromRequestPost()
    {
        $expectedResults = array('tst'=>'123');
        Phake::when($this->request)->getMethod()->thenReturn('POST');
        Phake::when($this->parameterBag)->all()->thenReturn($expectedResults);
        $this->request->request = $this->parameterBag;

        $this->model->fromRequest($this->request);

        $this->assertEquals($expectedResults, $this->getProtectedValue($this->model, 'parameters'));
        Phake::verify($this->parameterBag)->all();
    }


    /**
     * testGetSetColumnsValue
     */
    public function testGetSetColumnsValue()
    {
        $this->model->setColumns(array('test'));
        $this->assertEquals(array('test'), $this->model->getColumns());
    }

    /**
     * testAddColumn
     */
    public function testAddColumn()
    {
        $this->model->addColumn('test','123');
        $this->assertEquals(array('test'=>'123'), $this->model->getColumns());
    }

    /**
 * testGetSetEcho
 */
    public function testGetSetEcho()
    {
        $echo = 'test';
        $this->model->setEcho($echo);
        $this->assertEquals($echo, $this->model->getEcho());
    }

    /**
     * testGetSearchStringEmpty
     */
    public function testGetSearchStringEmpty()
    {
        $this->assertEquals('', $this->model->getSearchString());
    }

    /**
     * testGetSearchStringValue
     */
    public function testGetSearchStringValue()
    {
        $this->model->set('sSearch', 'test');
        $this->assertEquals('test', $this->model->getSearchString());
    }

    /**
     * testGetSetOffset
     */
    public function testGetSetOffset()
    {
        $this->model->setOffset(2);
        $this->assertEquals(2, $this->model->getOffset());

        $this->model->setOffset('5');
        $this->assertEquals(5, $this->model->getOffset());
    }

    /**
     * testGetSetDisplayLength
     */
    public function testGetSetDisplayLength()
    {
        $this->model->setDisplayLength(2);
        $this->assertEquals(2, $this->model->getDisplayLength());

        $this->model->setDisplayLength('5');
        $this->assertEquals(5, $this->model->getDisplayLength());
    }

    /**
     * testGetSetSortingLength
     */
    public function testGetSetSortingLength()
    {
        $this->model->setSortingLength(2);
        $this->assertEquals(2, $this->model->getSortingLength());

        $this->model->setSortingLength('5');
        $this->assertEquals(5, $this->model->getSortingLength());
    }

    /**
     * testGetSetSortingLength
     */
    public function testGetSetSortingDirection()
    {
        $this->model->setSortingDirection('asc', 1);
        $this->assertEquals('asc', $this->model->getSortingDirection(1));

        $this->model->setSortingDirection('desc', 3);
        $this->assertEquals('desc', $this->model->getSortingDirection(3));
    }

    /**
     * getCurrentPageDataProvider
     *
     * @return array
     */
    public function getCurrentPageDataProvider()
    {
        return array(
            array(0, 5, 0),
            array(1, 5, 0),
            array(5, 5, 1),
            array(6, 5, 1),
            array(10, 5, 2),
        );
    }

    /**
     * testGetCurrentPage
     *
     * @dataProvider getCurrentPageDataProvider
     */
    public function testGetCurrentPage($offset, $length, $result)
    {
        $this->model->setOffset($offset);
        $this->model->setDisplayLength($length);
        $this->assertEquals($result, $this->model->getCurrentPage());
    }

    /**
     * testIsSearchableEmpty
     */
    public function testIsSearchableEmpty()
    {
        $this->assertFalse($this->model->isSearchable(20));
    }

    /**
     * testIsSearchable
     */
    public function testIsSearchable()
    {
        $this->callProtected($this->model,'setVarByNameId', array('searchable', 2, 'true'));
        $this->assertTrue($this->model->isSearchable(2));
    }

    /**
     * testIsColumnSortableFalse
     */
    public function testIsColumnSortableFalse()
    {
        $this->assertFalse($this->model->isColumnSortable(20));
    }

    /**
     * testIsColumnSortableTrue
     */
    public function testIsColumnSortableTrue()
    {
        $this->callProtected($this->model,'setVarByNameId', array('sortCols', 2, 'true'));
        $this->assertTrue($this->model->isColumnSortable(2));
    }

    /**
     * testGetColumnSearch
     */
    public function testGetColumnSearch()
    {
        $this->callProtected($this->model,'setVarByNameId', array('searchCols', 2, 'test'));
        $this->assertEquals('test',$this->model->getColumnSearch(2));
    }

    /**
     * testHasColumnSearchFalse
     */
    public function testHasColumnSearchFalse()
    {
        $this->assertFalse($this->model->hasColumnSearch(1));
    }

    /**
     * testHasColumnSearchFalseSearchable
     */
    public function testHasColumnSearchFalseSearchable()
    {
        $this->callProtected($this->model,'setVarByNameId', array('searchable', 2, 'true'));
        $this->assertFalse($this->model->hasColumnSearch(2));
    }

    /**
     * testHasColumnSearchTrue
     */
    public function testHasColumnSearchTrue()
    {
        $this->callProtected($this->model,'setVarByNameId', array('searchable', 2, 'true'));
        $this->callProtected($this->model,'setVarByNameId', array('searchCols', 2, 'test'));
        $this->assertTrue($this->model->hasColumnSearch(2));
    }

    /**
     * testGetSortingColumnNull
     */
    public function testGetSortingColumnNull()
    {
        $this->assertEquals(null, $this->model->getSortingColumn(3));
    }

    /**
     * testGetSortingColumnValue
     */
    public function testGetSortingColumnValue()
    {
        $columns = array('test' => '123');
        $this->model->setColumns($columns);
        $this->callProtected($this->model,'setVarByNameId', array('sortCols', 2, 0));
        $this->assertEquals('test', $this->model->getSortingColumn(2));
    }

    /**
     * testGetSortingColumnsEmpty
     */
    public function testGetSortingColumnsEmpty()
    {
        $this->assertEquals(array(), $this->model->getSortingColumns());
    }

    /**
     * testGetSortingColumnNotSortable
     */
    public function testGetSortingColumnNotSortable()
    {
        $this->model->setSortingLength(1);
        $this->assertEquals(array(), $this->model->getSortingColumns());
    }

    /**
     * testGetSortingColumns
     */
    public function testGetSortingColumns()
    {
        $this->model->setSortingLength(1);
        $this->callProtected($this->model,'setVarByNameId', array('sortCols', 0, 'true'));
        $this->assertEquals(array('' => 'asc'), $this->model->getSortingColumns());
    }

    /**
     * testGetSearchColumnsEmpty
     */
    public function testGetSearchColumnsEmpty()
    {
        $this->assertEquals(array(), $this->model->getSearchColumns());
    }

    /**
     * testGetSearchColumnsSpecificColumn
     */
    public function testGetSearchColumnsSpecificColumn()
    {
        $expectedResult = array('a.id' => 'test');
        $columns = array(
            'a.id' => 'ID',
        );
        $this->model->setColumns($columns);
        $this->callProtected($this->model, 'setVarByNameId', array('searchable', 0, 'true'));
        $this->callProtected($this->model, 'setVarByNameId', array('searchCols', 0, 'test'));


        $results = $this->model->getSearchColumns();

        $this->assertEquals($expectedResult, $results);
    }

}