# Symfony2/Doctrine extension for DataTables jQuery Extension

This bundle adds DataTables ajax functionality to your Symfony2/Doctrine project.

![Build Status](http://rbsolutions.dyndns.org:8080/buildStatus/icon?job=DataTables)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/7fc69c53-74a5-4be0-9bea-c126436369df/mini.png)](https://insight.sensiolabs.com/projects/7fc69c53-74a5-4be0-9bea-c126436369df)

## Install
Add the package brown298\datatables to your composer.json
```json
{
    "require": {
        "brown298/data-tables-bundle": "dev-master"
    }
}
```
For more information about Composer, please visit [http://getcomposer.org](http://getcomposer.org/ "Composer")

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
                - %kernel.root_dir%/../vendor/datatables/datatables/media/js/jquery.js
                - %kernel.root_dir%/../vendor/datatables/datatables/media/js/jquery.dataTables.js
```

```jinja
  {# Resources\views\base.html.twig #}

  {% javascripts '@data_tables' %}
      <script type="text/javascript" src="{{ asset_url }}"></script>
  {% endjavascripts %}
```

For more examples and instructions, please visit [http://code.rbsolutions.us/datatables/](http://code.rbsolutions.us/datatables/ "DataTables")

