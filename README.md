Add custom fields to forms and entities (installation specifig).

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require cubetools/cube-custom-fields-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new CubeTools\CubeCustomFieldsBundle\CubeToolsCubeCustomFieldsBundle(),
        );

        // ...
    }

    // ...
}
```

Step 3: Configure the bundle
----------------------------

Then configure the bundle in `app/config/config.yml` of your project.

```yaml
imports:
    ...
    { require: custom_fields.yml }
    # or to ignore when not existing or invalid { require: custom_fields, ignore_errors: true }
```

`app/config/custom_fields.yml`
```yaml:
cube_custom_fields:
    entities:
        'XxBundle:Entity1':
            field_id1:
                field_type: [text|select|date|entity]
                field_label: 'label.for.a_field'
            field_idB:
                field_type: select
                field_label: label.select.something
                choices:
                    label1: value1
                    ...
        'YyBundle:Entity2':
            ...
    access_rights_table: 'XxBundle:AccessEntity'
```

Step X: display the filds
-------------------------
TODO
