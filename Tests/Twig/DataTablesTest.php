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
     * @Mock
     * @var \Brown298\DataTablesBundle\Model\DataTable\DataTableInterface
     */
    protected $dataTable;

    /**
     * @Mock
     * @var \Brown298\DataTablesBundle\MetaData\Column
     */
    protected $column;

    /**
     * @Mock
     * @var \Brown298\DataTablesBundle\MetaData\Table
     */
    protected $table;


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
        $this->setProtectedValue($this->service, 'params',array('table_template'=>'template','twigVars'=> array()));
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
        $this->setProtectedValue($this->service, 'params',
            array('script_template'=>'template','twigVars'=> array(), 'customParams' => array()));
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
        $this->assertInstanceOf('Twig_SimpleFunction', $results['addDataTable']);
    }

    /**
     * testGetFilters
     *
     * ensure that getFilters was defined
     */
    public function testGetFilters()
    {
        $results = $this->service->getFilters();
        $this->assertArrayHasKey('convertFunctions', $results);
        $this->assertInstanceOf('Twig_SimpleFilter', $results['convertFunctions']);
    }

    /**
     * convertFunctionsDataProvider
     *
     * @return array
     */
    public function convertFunctionsDataProvider()
    {
        return array(
            array(
                'sourceJson' => json_encode(array()),
                'resultJson' => '[]',
            ),
            array(
                'sourceJson' => json_encode(array('a' => 'a')),
                'resultJson' => '{"a":"a"}',
            ),
            array(
                'sourceJson' => json_encode(array('a' => 'a', 'b' => 'b')),
                'resultJson' => '{"a":"a","b":"b"}',
            ),
            array(
                'sourceJson' => json_encode(array(12 => 'a', 5 => 'b')),
                'resultJson' => '{"12":"a","5":"b"}',
            ),
            array(
                'sourceJson' => json_encode(array(12 => 'function() { print("test"); }', 5 => 'b')),
                'resultJson' => '{"12":function() { print("test"); },"5":"b"}',
            ),
            array(
                'sourceJson' => '{"d":"[\"a\",[\"b\",\"c\"]]"}',
                'resultJson' => '{"d":["a",["b","c"]]}',
            ),
            array(
                'sourceJson' => '{"a":"function(){return true;}","b":"[\"a\",[\"b\",\"c\"]]","d":"123"}',
                'resultJson' => '{"a":function(){return true;},"b":["a",["b","c"]],"d":"123"}',
            ),
        );
    }

    /**
     * testConvertFunctions
     *
     * @dataProvider convertFunctionsDataProvider
     */
    public function testConvertFunctions($sourceJson, $resultJson)
    {
        $result = $this->service->convertFunctions($sourceJson);
        $this->assertEquals($resultJson, $result);
    }

    /**
     * testAddDataTable
     */
    public function testAddDataTable()
    {
        $columns = array();
        $params  = null;

        $this->service->initRuntime($this->environment);
        $this->service->addDataTable($columns, $params);
        $resultParams = $this->getProtectedValue($this->service, 'params');

        $this->assertArrayHasKey('table_template', $resultParams);
        $this->assertArrayHasKey('script_template', $resultParams);
        $this->assertArrayHasKey('path', $resultParams);
    }

    /**
     * testBuildJsParamsEmpty
     *
     */
    public function testBuildJsParamsEmpty()
    {
        $results = $this->callProtected($this->service, 'buildJsParams');
        $this->assertEquals(array(), array_keys($results));
    }

    /**
     * testBuildJsParamsAssignsKeys
     */
    public function testBuildJsParamsAssignsKeys()
    {
        $this->setProtectedValue($this->service, 'params', array('bSort'=> false, 'aaData' => array('test'), 'customParams' => array('test'=> '123')));
        $results = $this->callProtected($this->service, 'buildJsParams');
        $this->assertEquals(array('aaData','bSort', 'test'), array_keys($results));
        $this->assertEquals(array('aaData'=>'["test"]','bSort'=>false, 'test'=>'123'), $results);
    }

    /**
     * testPathConversion
     *
     * ensures we convert path to ajax source
     *
     */
    public function testPathConversion()
    {
        $this->setProtectedValue($this->service, 'params', array('path'=> 'http://test'));
        $results = $this->callProtected($this->service, 'buildJsParams');
        $this->assertEquals(array('sAjaxSource'), array_keys($results));
        $this->assertEquals(array('sAjaxSource'=> 'http://test'), $results);
    }

    /**
     * testAaDataAoColumnDefsConversion
     *
     */
    public function testAaDataAoColumnDefsConversion()
    {
        $this->setProtectedValue($this->service, 'params', array('aaData'=> array('test'), 'aoColumnDefs' => array('defs')));
        $results = $this->callProtected($this->service, 'buildJsParams');
        $this->assertEquals(array('aaData','aoColumns'), array_keys($results));
        $this->assertEquals(array('aaData'=>'["test"]','aoColumns' => '["defs"]'), $results);
    }

    /**
     * testCustomSearchFormConversion
     *
     * ensures we output the custom search form javascript
     */
    public function testCustomSearchFormConversion()
    {
        $this->setProtectedValue($this->service, 'params', array('customSearchForm'=> 'testForm'));
        $results = $this->callProtected($this->service, 'buildJsParams');
        $this->assertEquals(array('fnServerData'), array_keys($results));
        $this->assertRegExp('/testForm/', $results['fnServerData'], 'Custom form name not found in fnServerData output');
    }

    /**
     * isJsonObjectDataProvider
     *
     * @return array
     */
    public function isJsonObjectDataProvider()
    {
        return array(
            array('[]', true),
            array('', false),
            array('{}', true),
            array('{"a":"b"}', true),
            array('{"a":"b","c":{"d":"e"}}', true),
            array('[["a","b"],"c","d"]', true),
        );
    }

    /**
     * testIsJsonObject
     *
     * @dataProvider isJsonObjectDataProvider
     *
     * @param $object
     * @param $expectedResult
     */
    public function testIsJsonObject($object, $expectedResult)
    {
        $result = $this->callProtected($this->service, 'isJsonObject', array($object));
        $this->assertEquals($expectedResult, $result, 'Error: isJsonObject returned ' . ($result ? 'True':'False') . ' on ' . $object);
    }

    /**
     * testAddDataTableGetsColumns
     */
    public function testAddDataTableGetsColumns()
    {
        $this->service->initRuntime($this->environment);

        $this->service->addDataTable($this->dataTable);

        Phake::verify($this->dataTable)->getColumns();
        $this->assertEquals($this->dataTable, $this->getProtectedValue($this->service, 'dataTable'));
    }

    /**
     * testBuildParamsCallsBuildDefaults
     */
    public function testBuildParamsCallsBuildDefaults()
    {
        $this->service = Phake::partialMock('\Brown298\DataTablesBundle\Twig\DataTables');
        $this->service->initRuntime($this->environment);
        Phake::when($this->service)->builParams()->thenReturn(null);

        $this->service->addDataTable($this->dataTable);

        Phake::verify($this->service)->buildDefaults();
    }

    /**
     * @return array
     */
    public function buildColumnDefProvider()
    {
        return array(
            array('sortable', 'bSort', true, false),
            array('sortable', 'bSort', false, true),
            array('searchable', 'bFilter', false, true),
            array('searchable', 'bFilter', true, false),
            array('visible', 'bVisible', false, true),
            array('visible', 'bVisible', true, false),
            array('class', 'sClass', 'testClass', true),
            array('class', 'sClass', null, false),
            array('width', 'sWidth', '20', true),
            array('width', 'sWidth', null, false),
            array('defaultSort', 'iDataSort', null, false),
        );
    }

    /**
     * testBuildColumnDef
     *
     * @param $fieldName
     * @param $arrayName
     * @param $value
     * @param $isset
     *
     * @dataProvider buildColumnDefProvider
     */
    public function testBuildColumnDef($fieldName, $arrayName, $value, $isset)
    {
        $this->column->$fieldName = $value;

        $result = $this->callProtected($this->service,'buildColumnDefs', array(array($this->column)));

        if ($isset) {
            $this->assertArrayHasKey($arrayName, $result[0]);
            $this->assertEquals($value, $result[0][$arrayName]);
        } else {
            $this->assertNull($result[0]);
        }
    }

    /**
     * testbuildDefaultsCallsBuildColumns
     */
    public function testbuildDefaultsCallsBuildColumns()
    {
        $meta = array('table'=> $this->table, 'columns' => array($this->column));
        $this->setProtectedValue($this->service,'dataTable', $this->dataTable);
        Phake::when($this->dataTable)->getMetaData()->thenReturn($meta);

        $this->callProtected($this->service, 'buildDefaults');

        $defaults = $this->getProtectedValue($this->service, 'defaults');
        $this->assertArrayHasKey('aoColumns', $defaults);
    }

    /**
     * @return array
     */
    public function buildDefaultsSetsDefaultsProvider() {
        return array(
            array('id', 'id', rand(0,100)),
            array('deferLoading', 'bDeferLoading', rand(0,100)),
            array('serverSideProcessing', 'bServerSide', rand(0,100)),
            array('info', 'bInfo', rand(0,100)),
            array('changeLength', 'bLengthChange', rand(0,100)),
            array('processing', 'bProcessing', rand(0,100)),
            array('displayLength', 'iDisplayLength', rand(0,100)),
            array('paginate', 'bPaginate', rand(0,100)),
            array('sortable', 'bSort', rand(0,100)),
            array('searchable', 'bFilter', rand(0,100)),
            array('paginationType', 'sPaginationType', rand(0,100)),
        );
    }

    /**
     * @param $name
     * @param $arrayName
     * @param $value
     * @dataProvider buildDefaultsSetsDefaultsProvider
     */
    public function testBuildDefaultsSetsDefaults($name, $arrayName, $value)
    {
        $this->table->$name = $value;
        $meta = array('table'=> $this->table, 'columns' => array($this->column));
        $this->setProtectedValue($this->service,'dataTable', $this->dataTable);
        Phake::when($this->dataTable)->getMetaData()->thenReturn($meta);

        $this->callProtected($this->service, 'buildDefaults');

        $defaults = $this->getProtectedValue($this->service, 'defaults');
        $this->assertEquals($value, $defaults[$arrayName]);
    }
} 