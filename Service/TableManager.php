<?php
namespace Brown298\DataTablesBundle\Service;

use Brown298\DataTablesBundle\MetaData\Table;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Util\ClassUtils;

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
     * @param ContainerInterface $container
     * @param AnnotationReader $reader
     * @param array $annotationPathSearch
     * @param EntityManager $em
     */
    public function __construct(ContainerInterface $container, AnnotationReader $reader, array $annotationPathSearch, EntityManager $em)
    {
        $this->annotationPathSearch = $annotationPathSearch;
        $this->reader    = $reader;
        $this->container = $container;
        $this->kernel    = $container->get('kernel');
        $this->em        = $em;
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

        foreach ($bundles as $name => $namespace) {
            $directories[$name] = $this->kernel->getBundle($name)->getPath();
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
                /** @todo add builder for yml */

                case 'xml':
                /** @todo add builder for xml */

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

        /** @todo load yml based tables */

        /** @todo load xml based tables */

        return $this->tables;
    }

    /**
     * buildAnnotations
     */
    protected function buildAnnotations()
    {
        $directories = $this->getPossibleAnnotationDirectories();
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
     * getPossibleDirectories
     *
     * gets an array of the possible data table directories
     *
     * @return array
     */
    protected function getPossibleAnnotationDirectories()
    {
        $directories = array();
        $bundleDirs  = $this->getBundleDirectories();

        foreach($bundleDirs as $bundle=>$directory) {
            foreach ($this->annotationPathSearch as $dir) {
                $path = $directory . DIRECTORY_SEPARATOR . $dir;
                if (is_dir($path)) { $directories[$bundle][$dir] = $path; }
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
            $bundleNamespace = substr(get_class($realBundle), 0, -strlen($bundle)) . $dir . $relativePath;
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