# Symfony2/Doctrine extension for DataTables jQuery Extension

This bundle adds DataTables ajax functionality to your Symfony2/Doctrine project.

## Install
Add the package brown298\datatables to your composer.json
```json
{
    "require": {
        "brown298/data-tables-bundle": "dev-master"
    }
}
```
For more information about Composer, please visit http://getcomposer.org

## Configure

### Add Brown298DataTablesBundle to your application kernel

```php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new Brown298\DataTablesBundle\Brown298DataTablesBundle(),
        // ...
    );
}
```

### Assetic

As part of the composer requirements, both jQuery and DataTables are added to the vendor directory.  You are welcome
to use those files, or install your own.  But both jQuery and DataTables are required to be added to your templates
to use this bundle.

``` yml
# config.yml

assetic:
    assets:
        data_tables:
            inputs:
                - %kernel.root_dir%/../vendor/jquery/jquery/jquery-1.9.1.js
                - %kernel.root_dir%/../vendor/datatables/datatables/media/js/jquery.dataTables.js
```

```jinja
  {# Resources\views\base.html.twig #}

  {% javascripts '@data_tables' %}
      <script type="text/javascript" src="{{ asset_url }}"></script>
  {% endjavascripts %}
```

## Usage examples:

Currently dataTables can paginate :

- `array`
- `Doctrine\ORM\QueryBuilder`

### Option 1 Controller Extension

#### Controller

```php
use Brown298\DataTablesBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @var array
     */
    protected $columns = array(
        'user.id'   => 'Id',
        'user.name' => 'Name,
    );

    /**
     * getQueryBuilder
     *
     * @param Request $request
     *
     * @return null
     */
    public function getQueryBuilder(Request $request)
    {
        $em             = $this->get('doctrine.orm.entity_manager')
        $userRepository = $em->getRepository('ExampleBundle\Entity\User');
        $qb = $userRepository->createQueryBuilder('user')
                ->andWhere('user.deleted = false');

        return $qb;
    }

    /**
     * dataAction
     *
     * @route("/ajax", name="show_ajax")
     *
     * @param Request $request
     * @param null $dataFormatter
     *
     * @return JsonResponse
     */
    public function dataAction(Request $request, $dataFormatter = null)
    {
        $renderer = $this->get('templating');

        return parent::dataAction($request, function($data) use ($renderer) {
            $count   = 0;
            $results = array();

            foreach ($data as $row) {
                $results[$count][] = $row['id'];
                $results[$count][] = $renderer->render('ExampleBundle:Default:nameFormatter.html.twig', array('name' => $row['name']));
                $count += 1;
            }

            return $results;
        });
    }

    /**
     * indexAction
     *
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        return array(
            'columns'       => $this->columns,
        );
    }
}
```

#### View

```jinja
  {# Default\index.html.twig #}

  {% block body %}

    {{ addDataTable(columns, {
            'path':          path('show_ajax'),
            'bLengthChange': 1,
            'bInfo':         1,
            'bFilter':       1,
        }) }}

  {% endblock %}
```

```jinja
  {# Default\nameFormatter.html.twig #}

  <span class='strong'>{{ name }} </span>
```

### Option 2 Model Class

Instead of dirtying up the controller and posting to a seperate action, we can simplify the implementation using
a model class.  This class will contain all the information necessary to deal with the data request and leaves the
controller very clean.

#### Model
```php
class UserTable extens AbstractDataTable implements DataTableInterface
{
    /**
     * @var array
     */
    protected $columns = array(
        'user.id'   => 'Id',
        'user.name' => 'Name,
    );

    /**
     * getFormatter
     *
     * @return \Closure
     */
    public function getFormatter()
    {
        $renderer = $this->containter->get('templating');
        return function($data) use ($renderer) {
            $count   = 0;
            $results = array();

            foreach ($data as $row) {
                $results[$count][] = $row['id'];
                $results[$count][] = $renderer->render('ExampleBundle:Default:nameFormatter.html.twig', array('name' => $row['name']));
                $count += 1;
            }

            return $results;
        };
    }

    /**
     * getQueryBuilder
     *
     * @param Request $request
     *
     * @return null
     */
    public function getQueryBuilder(Request $request)
    {
        $userRepository = $this->em->getRepository('ExampleBundle\Entity\User');
        $qb = $userRepository->createQueryBuilder('user')
                ->andWhere('user.deleted = false');

        return $qb;
    }
}
```

#### Controller
```php
class DefaultController extends Controller
{
    /**
     * indexAction
     *
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $request   = $this->getRequest();

        // process the data table
        $dataTable = new UserTable($em);
        $dataTable->setContainer($this->getContainer());
        if ($response = $dataTable->ProcessRequest($request)) {
            return $response;
        }

        // display html
        return array(
            'columns'       => $dataTable->getColumns(),
        );
    }
}
```

#### View
```jinja
  {# Default\index.html.twig #}

  {% block body %}

    {{ addDataTable(columns, {
            'bLengthChange': 1,
            'bInfo':         1,
            'bFilter':       1,
        }) }}

  {% endblock %}
```

```jinja
  {# Default\nameFormatter.html.twig #}

  <span class='strong'>{{ name }} </span>
```