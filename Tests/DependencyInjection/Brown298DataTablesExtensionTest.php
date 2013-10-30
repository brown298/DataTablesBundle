<?php
namespace Brown298\DataTablesBundle\Test\DependencyInjection;

use Brown298\DataTablesBundle\DependencyInjection\Brown298DataTablesExtension;
use Phake;
use Brown298\DataTablesBundle\Test\AbstractBaseTest;

/**
 * Class Brown298DataTablesExtensionTest
 * @package Brown298\DataTablesBundle\Test\DependencyInjection
 * @author  John Brown <brown.john@gmail.com>
 */
class Brown298DataTablesExtensionTest extends AbstractBaseTest
{

    /**
     * @var Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $containerBuilder;

    /**
     * @var Brown298\DataTablesBundle\DependencyInjection\Brown298DataTablesExtension
     */
    protected $extension;

    /**
     * @var Symfony\Component\DependencyInjection\ParameterBag\ParameterBag
     */
    protected $parameterBag;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->extension = new Brown298DataTablesExtension();
    }

    /**
     * testLoad
     */
    public function testLoad()
    {
        $configs = array();
        $this->containerBuilder = Phake::mock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $this->parameterBag     = Phake::mock('Symfony\Component\DependencyInjection\ParameterBag\ParameterBag');

        Phake::when($this->containerBuilder)->getParameterBag()->thenReturn($this->parameterBag);

        $this->extension->load($configs, $this->containerBuilder);

        Phake::verify($this->containerBuilder)->addResource(Phake::anyParameters());
    }
}