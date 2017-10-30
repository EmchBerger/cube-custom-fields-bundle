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

Add the routes in `app/config/routing.yml` of your project.

```yaml
    ...
cube_custom_fields:
    resource: "@CubeCustomFieldsBundle/Resources/config/routing/all.yml"
```

Then configure the bundle in `app/config/config.yml` of your project.

```yaml
imports:
    ...
    { resource: custom_fields.yml }
    # or to ignore when not existing or invalid { resource: custom_fields.yml, ignore_errors: true }
```

`app/config/custom_fields.yml`
```yaml:
cube_custom_fields:
    entities:
        XxBundle\Entities\Entity1:
            field_id1:
                field_type: SomeFormType
                     # like Symfony\Component\Form\Extension\Core\Type\[TextType|SelectType|DateTimeType]|Symfony\Bridge\Doctrine\Form\Type\EntityType]
                label: 'label.for.a_field'
                form_options:
                     # any form option
                     label: label for in form # overwrites label from above
            field_idB:
                type: Symfony\Component\Form\Extension\Core\Type\SelectType
                label: label.select.something
                form_options:
                    choices:
                        label1: value1
                        ...
        SomeTool\YyBundle\Entity\Entity2:
            responsibles: # this links as m:n to an existing entity type
                type: Symfony\Bridge\Doctrine\Form\Type\EntityType
                label: 'Responsible Persons'
                field_options:
                    required: false
                    multiple: true
                    class: 'AppBundle:User'
                    attr:
                        class: select2
                        placeholder: Select responsible persons
            selections: # this links to the custom fields table itself, giving access to all TextCustomField entities with fieldId = predef_1
                type: Symfony\Bridge\Doctrine\Form\Type\EntityType
                label: 'Predefined select options'
                filter: predef_1 # this is the name of the referred custom field
                field_options:
                    required: false
                    multiple: false
                    class: 'CubeTools\CubeCustomFieldsBundle\Entity\TextCustomField'
                    attr:
                        class: select2
                        placeholder: Select from set of options
            owner: # special case for ajax retrievable select2 boxes (Using Tetranz\Select2EntityBundle)
                type: Tetranz\Select2EntityBundle\Form\Type\Select2EntityType # contains the class of the form type used
                label: 'Owned by'
                filters: # this can be used to filter for specific fields on the target entity (e.g. only activated users etc.). Note that this is not the same field as for "normal" entity type custom fields (it is "filter" there).
                    enabled: 1
                field_options:
                    required: false
                    multiple: false
                    class: 'AppBundle:User' # contains the class of the objects visible to the user (the REAL entities)
                    minimum_input_length: 0
                    page_limit: 10
                    scroll: true
                    allow_clear: false
                    delay: 250
                    cache: true
                    cache_timeout: 500
                    placeholder: Please select
                    language: de
                    attr:
                        style: width:100%
                        data-role: none
    # access_rights_table: 'XxBundle:AccessEntity'
```

Step X: link custom fields to entities
--------------------------------------
Allow to link custom fields to your entity.
```php
class Xxx
{
    use \CubeTools\CubeCustomFieldsBundle\CustomFieldsEntityHook;
}
```

Step X: show fields in the forms
--------------------------------
Add the custom fields to your forms.
```php
class XxxType extends FormType
{
    ...
    public function buildForm(...)
    {
        ...
        $customFieldsService = $options['customFieldsService']; // or configure your form as a service
        $customFieldsService->addCustomFields($form);
    }
    ...
}

class XzyController extends Controller
{
    ...
    public function zxyAction(CubeTools\CubeCustomFieldsBundle\Form\CustomFieldsFormService $customFieldsService)
    {
        // or $customFieldsService = $this->get('cube_custom_fields.form_fields');
        $form = $this->createForm(XxxType::class, null, array('customFieldsService' => $customFieldsService;
        ...
    }
```

Step X: display the fields
--------------------------

```twig
import dynamicFields.macro.twig as dynFld

...
{{ dynFld.title_column(entities) }}
...
{{ dynFld.filter_column(entities) }}
...
{% for(entity in entites) %}
    ...
    {{ dynFld.value_column(entity) }}
```
