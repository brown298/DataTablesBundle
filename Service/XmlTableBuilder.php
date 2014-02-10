<?php
namespace Brown298\DataTablesBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class XmlTableBuilder
 * @package Brown298\DataTablesBundle\Service
 */
class XmlTableBuilder implements TableBuilderInterface
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $tableConfig;

    /**
     * @var string
     */
    protected $tableId;

    /**
     * @param ContainerInterface $container
     * @param EntityManager $em
     * @param $tableId
     * @param mixed $tableConfig
     */
    public function __construct(ContainerInterface $container, EntityManager $em, $tableId, $tableConfig)
    {
        $this->container   = $container;
        $this->tableConfig = $tableConfig;
        $this->em          = $em;
        $this->tableId     = $tableId;
    }

    /**
     * build
     *
     * @param array $args
     * @return mixed
     */
    public function build(array $args = array())
    {
        // TODO: Implement build() method.
    }
}