<?php
namespace Brown298\DataTablesBundle\Service;

use Brown298\DataTablesBundle\MetaData\Table;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Util\ClassUtils;
use Symfony\Component\Yaml\Parser;

/**
 * Class TableManager
 *
 * @package Brown298\DataTablesBundle\Service
 * @author  John Brown <brown.john@gmail.com>
 */
class TableManager
{
    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    private $reader;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Symfony\Component\HttpKernel\Kernel;
     */
    private $kernel;

    /**
     * @var array
     */
    public $annotationPathSearch = array();

    /**
     * @var array
     */
    protected $configPathSearch = array();

    /**
     * @var array tables found
     */
    private $tables = array();

    /**
     * @var array
     */
    private $builtTables = array();

    /**
     * @var EntityManger
     */
    protected $em;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @param ContainerInterface $container
     * @param AnnotationReader $reader
     * @param array $configPathSearch
     * @param array $annotationPathSearch
     * @param EntityManager $em
     */
    public function __construct(
        ContainerInterface $container,
        AnnotationReader   $reader,
        array              $configPathSearch,
        array              $annotationPathSearch,
        EntityManager      $em
    ) {
        $this->annotationPathSearch = $annotationPathSearch;
        $this->reader               = $reader;
        $this->container            = $container;
        $this->kernel               = $container->get('kernel');
        $this->em                   = $em;
        $this->configPathSearch     = $configPathSearch;
    }

    /**
     * @return mixed
     */
    protected function getBundles()
    {
        return $this->container->getParameter('kernel.bundles');
    }

    /**
     * getBundleDirectories
     *
     * gets an array of the bundle directories to search
     *
     * @return array
     */
    protected function getBundleDirectories()
    {
        $directories = array();
        $bundles     = $this->getBundles();

        if (count($bundles) > 0) {
            foreach ($bundles as $name => $namespace) {
                $directories[$name] = $this->kernel->getBundle($name)->getPath();
            }
        }

        return $directories;
    }

    /**
     * hasTable
     *
     * determines if a table exists
     *
     * @param $id
     * @return bool
     */
    public function hasTable($id)
    {
        $tables = $this->all();
        return array_key_exists($id, $tables);
    }

    /**
     * @param $id
     * @return bool
     */
    public function hasBuiltTable($id)
    {
        if (!$this->hasTable($id)) {
            return false;
        }
        return array_key_exists($id, $this->builtTables);
    }

    /**
     * getTable
     *
     * @param $id
     * @return null
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     */
    public function getTable($id) {
        if ($this->hasTable($id)) {
            if ($this->hasBuiltTable($id)) {
                return $this->builtTables[$id];
            }
            $table = $this->tables[$id];

            switch ($table['type']) {
                case 'annotation':
                    $tableBuilder = new AnnotationTableBuilder($this->container, $this->em, $this->reader, $table['file']);
                    break;
                case 'yml':
                    $tableBuilder = new YmlTableBuilder($this->container, $this->em, $id,  $table['contents']);
                    break;
                case 'xml':
                    $tableBuilder = new XmlTableBuilder($this->container, $this->em, $id, $table['contents']);
                    break;
                default:
                    Throw new InvalidArgumentException('DataTable ' . $id . ' does not have a type specified');
                    break;
            }

            $this->builtTables[$id] = $tableBuilder->build(func_get_args());

            return $this->builtTables[$id];
        } else {
            Throw new InvalidArgumentException('DataTable ' . $id . ' does not exist');
        }

        return null;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->getTables();
    }


    /**
     * getTables
     */
    protected function getTables()
    {
        if (!empty($this->tables)) {
            return $this->tables;
        }

        $this->buildAnnotations();
        $this->buildConfig();
        $this->buildConfig('xml');

        return $this->tables;
    }

    /**
     * buildYml
     */
    protected function buildConfig($type = 'yml')
    {
        $paths       = array('Resources' . DIRECTORY_SEPARATOR . 'config');
        $directories = $this->getPossibleDirectories($paths);

        foreach ($directories as $directory) {
            foreach ($directory as $path) {
                foreach ($this->configPathSearch as $file) {
                    $currentPath = $path . DIRECTORY_SEPARATOR . $file . '.' . $type;
                    if (file_exists($currentPath)) {
                        switch($type) {
                            case 'yml':
                                $this->tables = array_merge($this->tables, $this->parseYml($currentPath));
                                break;
                            case 'xml':
                                $this->tables = array_merge($this->tables, $this->parseXml($currentPath));
                                break;
                        }

                    }
                }
            }
        }
    }

    /**
     * getYmlParser
     *
     * @return Parser
     */
    protected function getYmlParser()
    {
        if ($this->parser === null) {
            $this->parser = new Parser();
        }
        return $this->parser;
    }

    /**
     * @param $filePath
     * @return array
     */
    protected function parseYml($filePath)
    {
        $parser    = $this->getYmlParser();
        $contents  = $parser->parse(file_get_contents($filePath));
        $tables   = array();
        if (is_array($contents)) {
            foreach($contents as $tableId => $tableConfig) {
                $tables[$tableId] = array('type' => 'yml', 'file' => $filePath, 'contents' => $tableConfig);
            }
        }

        return $tables;
    }

    /**
     * @param $filePath
     * @return array
     * @throws \RuntimeException
     */
    protected function parseXml($filePath)
    {
        $tables  = array();
        $parser = $this->getXml($filePath);

        if ($parser == false) {
            throw new \RuntimeException('Error loading config file:' . $filePath);
        }

        foreach($parser->children() as $table) {
            $id = null;
            foreach ($table->attributes() as $key => $value) {
                if ($key == 'id') {
                    $id = $value->__toString();
                }
            }

            if ($id == null) {
                throw new \RuntimeException('XML DataTable definitions require an id attribute');
            }

            $tables[$id] = array('type' => 'xml', 'file' => $filePath, 'contents' => $table);
        }

        return $tables;
    }

    /**
     * @param $filePath
     * @return \SimpleXMLElement
     * @throws \RuntimeException
     */
    protected function getXml($filePath)
    {
        $parser = @simplexml_load_file($filePath);

        return $parser;
    }


    /**
     * buildAnnotations
     */
    protected function buildAnnotations()
    {
        $directories = $this->getPossibleDirectories($this->annotationPathSearch);
        foreach ($directories as $bundle => $directory) {
            foreach ($directory as $dir => $path) {

                // load annotations
                $finder  = new Finder();
                $finder->files()->in($path)->name('*.php');
                $files = $this->getAnnotationTablesInDir($bundle, $dir, $finder);
                if (!empty($files)) {
                    $this->tables = array_merge($this->tables, $files);
                }
            }
        }
    }


    /**
     * @param $searchPath
     * @return array
     */
    protected function getPossibleDirectories(array $searchPath)
    {
        $directories = array();
        $bundleDirs  = $this->getBundleDirectories();

        foreach($bundleDirs as $bundle=>$directory) {
            foreach ($searchPath as $dir) {
                $path = $directory . DIRECTORY_SEPARATOR . $dir;

                if (is_dir($path)) {
                    $directories[$bundle][$dir] = $path;
                }
            }
        }

        return $directories;
    }

    /**
     * loads the tables in a directory
     *
     * @param $bundle
     * @param $dir
     * @param Finder $finder
     * @return array
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     */
    protected function getAnnotationTablesInDir($bundle, $dir, Finder $finder)
    {
        $tables = array();
        foreach($finder as $file) {
            $realBundle      = $this->kernel->getBundle($bundle);
            $relativePath    = str_replace('/','\\',$file->getRelativePath());
            if (strlen($relativePath) > 0) $relativePath = '\\' . $relativePath;
            $bundleLong      = get_class($realBundle);
            $bundleNamespace = substr(get_class($realBundle), 0, -(strlen($bundleLong) - strrpos($bundleLong, '\\'))) . '\\' . $dir . $relativePath;
            $shortName = basename($file->getRelativePathname(),'.php');
            $class = $bundleNamespace . '\\' . $shortName;
            $refl = new \ReflectionClass($class);
            if (!$refl->isAbstract()) {
                $annotations = $this->reader->getClassAnnotations($refl);
                foreach ($annotations as $annotation) {
                    if ($annotation instanceof Table) {
                        if ($annotation->id == null) {
                            throw new InvalidArgumentException('DataTables requires an "id" attribute be provided');
                        }
                        $annotation->class = $refl->getName();
                        $tables[$annotation->id] = array('type' => 'annotation', 'file' => $annotation);
                    }
                }
            }
        }

        return $tables;
    }


}