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


}