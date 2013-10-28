<?php
namespace Brown298\DataTablesBundle\Tests\Model;

use Brown298\DataTablesBundle\Model\ResponseParameterBag;
use Brown298\DataTablesBundle\Test\AbstractBaseTest;
use Phake;

/**
 * Class ResponseParameterBagTest
 *
 * @package Brown298\DataTablesBundle\Tests\Model
 * @author  John Brown <brown.john@gmail.com>
 */
class ResponseParameterBagTest extends AbstractBaseTest
{
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

        $this->model   = new ResponseParameterBag();
    }

    /**
     * testGetSetTotal
     */
    public function testGetSetTotal()
    {
        $this->model->setTotal(3);
        $this->assertEquals(3, $this->model->getTotal());

        $this->model->setTotal('4');
        $this->assertEquals(4, $this->model->getTotal());
    }

    /**
     * testGetSetDisplayTotal
     */
    public function testGetSetDisplayTotal()
    {
        $this->model->setDisplayTotal(3);
        $this->assertEquals(3, $this->model->getDisplayTotal());

        $this->model->setDisplayTotal('4');
        $this->assertEquals(4, $this->model->getDisplayTotal());
    }

    /**
     * testGetSetData
     */
    public function testGetSetData()
    {
        $data = array('test');
        $this->model->setData($data);
        $this->assertEquals($data, $this->model->getData());
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
     * testSetRequest
     */
    public function testSetRequest()
    {
        $request = \Phake::mock('Brown298\DataTablesBundle\Model\RequestParameterBag');
        $this->model->setRequest($request);
        phake::verify($request)->getEcho();
    }

    /**
     * testAllEmpty
     */
    public function testAllEmpty()
    {
        $results = $this->model->all();

        $this->assertArrayHasKey('aaData', $results);
        $this->assertArrayHasKey('sEcho', $results);
        $this->assertArrayHasKey('iTotalRecords', $results);
        $this->assertArrayHasKey('iTotalDisplayRecords', $results);
    }

    /**
     * testAllHasTotalRecords
     */
    public function testAllHasTotalRecords()
    {
        $value = 2;
        $name  = 'iTotalRecords';
        $this->model->setTotal($value);
        $results = $this->model->all();
        $this->assertArrayHasKey($name, $results);
        $this->assertEquals($value, $results[$name]);
    }

    /**
     * testAllHasDisplayTotalRecords
     */
    public function testAllHasDisplayTotalRecords()
    {
        $value = 2;
        $name  = 'iTotalDisplayRecords';
        $this->model->setDisplayTotal($value);
        $results = $this->model->all();
        $this->assertArrayHasKey($name, $results);
        $this->assertEquals($value, $results[$name]);
    }

}