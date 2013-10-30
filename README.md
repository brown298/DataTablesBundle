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

## Usage examples:

Currently dataTables can paginate :

- `array`
- `Doctrine\ORM\QueryBuilder`

### Controller

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

### View

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
