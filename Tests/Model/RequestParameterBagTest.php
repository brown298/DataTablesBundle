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
     * setUp
     */
    public function setUp()
    {
        parent::setUp();

        $this->request = Phake::mock('Symfony\Component\HttpFoundation\Request');
        $this->model   = new RequestParameterBag();
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
}