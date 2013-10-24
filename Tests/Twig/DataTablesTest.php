<?php
namespace Brown298\DataTablesBundle\Tests\Twig;

use \Brown298\DataTablesBundle\Test\AbstractBaseTest;
use \Brown298\DataTablesBundle\Twig\DataTables;
use \Phake;

/**
 * Class DataTablesTest
 *
 * @package Brown298\DataTablesBundle\Tests\Twig
 * @author  John Brown <brown.john@gmail.com>
 */
class DataTablesTest extends AbstractBaseTest
{
    /**
     * @Mock
     * @var Twig_Environment
     */
    private $environment;

    /**
     * @var \Brown298\DataTablesBundle\Twig\DataTables
     */
    protected $service;

    /**
     * setUp
     *
     */
    public function setUp()
    {
        parent::setUp();
        $this->service = new DataTables();
    }

    /**
     * testCreate
     *
     */
    public function testCreate()
    {
        $this->assertInstanceOf('\Brown298\DataTablesBundle\Twig\DataTables', $this->service);
    }

    /**
     * testGetName
     *
     */
    public function testGetName()
    {
        $this->assertEquals('data_tables', $this->service->getName());
    }

    /**
     * testRenderTable
     *
     * ensure we get our output from twig
     */
    public function testRenderTable()
    {
        $this->setProtectedValue($this->service, 'params',array('table_template'=>'template'));
        Phake::when($this->environment)->render(Phake::anyParameters())->thenReturn('test');
        $this->service->initRuntime($this->environment);

        $result = $this->service->renderTable();

        $this->assertEquals('test', $result);
        Phake::verify($this->environment)->render('template', $this->anything());
    }

    /**
     * testRenderJs
     *
     */
    public function testRenderJs()
    {
        $this->setProtectedValue($this->service, 'params',array('script_template'=>'template'));
        Phake::when($this->environment)->render(Phake::anyParameters())->thenReturn('test');
        $this->service->initRuntime($this->environment);

        $result = $this->service->renderJs();

        $this->assertEquals('test', $result);
        Phake::verify($this->environment)->render('template', $this->anything());
    }

    /**
     * testGetFunctions
     *
     * ensures get functions returns the expected methods
     */
    public function testGetFunctions()
    {
        $results = $this->service->getFunctions();
        $this->assertArrayHasKey('addDataTable', $results);
        $this->assertInstanceOf('Twig_Function_Method', $results['addDataTable']);
    }

} 