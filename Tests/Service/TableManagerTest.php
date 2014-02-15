<?php
namespace Brown298\DataTablesBundle\Tests\Service;

use Brown298\DataTablesBundle\Service\TableManager;
use Phake;
use Brown298\DataTablesBundle\Test\AbstractBaseTest;

/**
 * Class TableManagerTest
 * @package Brown298\DataTablesBundle\Tests\Service
 * @author  John Brown <brown.john@gmail.com>
 */
class TableManagerTest extends AbstractBaseTest
{
    /**
     * @var \Doctrine\ORM\EntityManger
     */
    protected $em;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected $reader;

    /**
     * @var \Brown298\DataTablesBundle\Service\TableManager
     */
    protected $tableManager;

    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    private $kernel;

    /**
     * @var \Symfony\Component\HttpKernel\Bundle\Bundle
     */
    protected $bundle;

    /**
     * @var \Symfony\Component\Yaml\Parser
     */
    protected $parser;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->container = Phake::mock('\Symfony\Component\DependencyInjection\ContainerInterface');
        $this->em        = Phake::mock('\Doctrine\ORM\EntityManager');
        $this->reader    = Phake::mock('\Doctrine\Common\Annotations\AnnotationReader');
        $this->kernel    = Phake::mock('\Symfony\Component\HttpKernel\Kernel');
        $this->bundle    = Phake::mock('\Symfony\Component\HttpKernel\Bundle\Bundle');
        $this->parser    = Phake::mock('\Symfony\Component\Yaml\Parser');
        Phake::when($this->container)->get('kernel')->thenReturn($this->kernel);

        $this->tableManager = new TableManager($this->container, $this->reader, array(), array(), $this->em);
    }

    /**
     * testCreate
     */
    public function testCreate()
    {
        $this->assertInstanceOf('Brown298\DataTablesBundle\Service\TableManager', $this->tableManager);
    }

    /**
     * testGetBundles
     */
    public function testGetBundles()
    {
        $expectedResults = 'test';
        Phake::when($this->container)->getParameter('kernel.bundles')->thenReturn($expectedResults);

        $result  = $this->callProtected($this->tableManager,'getBundles');

        $this->assertEquals($expectedResults, $result);
    }

    /**
     * testHasTable
     */
    public function testHasTable()
    {
        $tables = array('test'=>'table');
        $this->setProtectedValue($this->tableManager,'tables', $tables);
        $this->assertTrue($this->tableManager->hasTable('test'));
        $this->assertFalse($this->tableManager->hasTable('testing'));
    }

    /**
     * testHasBuiltTableFalse
     */
    public function testHasBuiltTableFalse()
    {
        $tables = array();
        $this->setProtectedValue($this->tableManager,'tables', $tables);
        $this->assertFalse($this->tableManager->hasBuiltTable('test'));
    }

    /**
     * testHasBuiltTableTrue
     */
    public function testHasBuiltTableTrue()
    {
        $tables = array('test' =>'value');
        $this->setProtectedValue($this->tableManager,'tables', $tables);
        $this->setProtectedValue($this->tableManager,'builtTables', $tables);
        $this->assertTrue($this->tableManager->hasBuiltTable('test'));
    }

    /**
     * testGetBundleDirectories
     */
    public function testGetBundleDirectories()
    {
        $expectedResult = 'test';
        $bundles        = array('bundle' => 'namespace');
        Phake::when($this->container)->getParameter('kernel.bundles')->thenReturn($bundles);
        Phake::when($this->kernel)->getBundle('bundle')->thenReturn($this->bundle);
        Phake::when($this->bundle)->getPath()->thenReturn($expectedResult);

        $result = $this->callProtected($this->tableManager,'getBundleDirectories');

        $this->assertEquals(array('bundle'=> $expectedResult), $result);
    }

    /**
     * testGetPossibleDirectoriesNonDir
     */
    public function testGetPossibleDirectoriesNonDir()
    {
        $searchPath     = array('dir');
        $expectedResult = 'test';
        $bundles        = array('bundle' => 'namespace');
        Phake::when($this->container)->getParameter('kernel.bundles')->thenReturn($bundles);
        Phake::when($this->kernel)->getBundle('bundle')->thenReturn($this->bundle);
        Phake::when($this->bundle)->getPath()->thenReturn($expectedResult);

        $result = $this->callProtected($this->tableManager, 'getPossibleDirectories', array($searchPath));

        $this->assertEquals(array(), $result);
    }

    /**
     * testGetPossibleDirectoriesDir
     */
    public function testGetPossibleDirectoriesDir()
    {
        $searchPath     = array('tmp');
        $expectedResult = '/';
        $bundles        = array('bundle' => '');
        Phake::when($this->container)->getParameter('kernel.bundles')->thenReturn($bundles);
        Phake::when($this->kernel)->getBundle('bundle')->thenReturn($this->bundle);
        Phake::when($this->bundle)->getPath()->thenReturn($expectedResult);

        $result = $this->callProtected($this->tableManager, 'getPossibleDirectories', array($searchPath));

        $this->assertEquals(array('bundle' => array( 'tmp'=> '//tmp' )), $result);
    }

    /**
     * testGetTableThrowsErrorWhenTableMissing
     * @expectedException InvalidArgumentException
     */
    public function testGetTableThrowsErrorWhenTableMissing()
    {
        $this->tableManager->getTable('test');
    }

    /**
     * testGetTableExisting
     */
    public function testGetTableExisting()
    {
        $this->setProtectedValue($this->tableManager, 'tables', array('test'=>'value'));
        $this->setProtectedValue($this->tableManager, 'builtTables', array('test'=>'builtValue'));
        $result = $this->tableManager->getTable('test');
        $this->assertEquals('builtValue', $result);
    }

    /**
     * testParseXmlNonExistantFile
     * @expectedException RuntimeException
     */
    public function testParseXmlNonExistantFile()
    {
        $this->callProtected($this->tableManager, 'parseXml', array('fakeFile'));
    }

    /**
     * testGetXml
     */
    public function testGetXml()
    {
        $result = $this->callProtected($this->tableManager, 'getXml', array(__DIR__  . '/../../Test/validTable.xml'));
        $this->assertInstanceOf('\SimpleXMLElement', $result);
    }

    /**
     * testParseXml
     */
    public function testParseXml()
    {
        $result = $this->callProtected($this->tableManager, 'parseXml', array(__DIR__  . '/../../Test/validTable.xml'));
        $this->assertGreaterThan(0, count($result));
        $this->assertArrayHasKey('xmlDataTable', $result);
        $this->assertArrayHasKey('type', $result['xmlDataTable']);
        $this->assertArrayHasKey('file', $result['xmlDataTable']);
        $this->assertArrayHasKey('contents', $result['xmlDataTable']);
    }

    /**
     * testParseXmlThrowsExceptionMissingId
     * @expectedException RuntimeException
     */
    public function testParseXmlThrowsExceptionMissingId()
    {
        $this->callProtected($this->tableManager, 'parseXml', array(__DIR__  . '/../../Test/invalidTable.xml'));
    }

    /**
     * testGetYmlParserReturnsParser
     */
    public function testGetYmlParserReturnsParser()
    {
        $this->assertInstanceOf('\Symfony\Component\Yaml\Parser', $this->callProtected($this->tableManager,'getYmlParser'));
    }

    /**
     * testParseYml
     */
    public function testParseYml()
    {
        $tableConfig = array(
            'ymlDataTable' => 'config'
        );
        $this->setProtectedValue($this->tableManager,'parser',$this->parser);
        Phake::when($this->parser)->parse(Phake::anyParameters())->thenReturn($tableConfig);

        $result = $this->callProtected($this->tableManager, 'parseYml', array(__DIR__  . '/../../Test/invalidTable.xml'));

        $this->assertArrayHasKey('ymlDataTable', $result);
        $this->assertArrayHasKey('type', $result['ymlDataTable']);
        $this->assertArrayHasKey('file', $result['ymlDataTable']);
        $this->assertArrayHasKey('contents', $result['ymlDataTable']);
    }
}