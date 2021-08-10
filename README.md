# DataModel

PHP data access and serving class

<p align="center">
<a href="https://packagist.org/packages/kosatyi/datamodel"><img src="https://poser.pugx.org/kosatyi/datamodel/version" /></a>
<a href="https://packagist.org/packages/kosatyi/datamodel"><img src="https://poser.pugx.org/kosatyi/datamodel/downloads"/></a>
<a href="https://packagist.org/packages/kosatyi/datamodel"><img src="https://poser.pugx.org/kosatyi/datamodel/license" /></a>
</p>

If you just want to access the array of model data from the context of a container object, 
you should use either the `attr` method

```php
    use Kosatyi\DataModel as Model;
    $model = new Model();
    $model->data([
        'prop' => 'value',
        'list' => [
            'item0','item1','item2'
        ],
        'map'  = [
            'prop1'=> 'value1',
            'prop2'=> 'value2' 
        ]
    ]);
    echo $model->attr('prop');
    echo $model->attr('list.0');
    echo $model->attr('map.prop1');
    $model->attr('list.1','item1-changed');
    $model->attr('prop','value-changed');
    $model->attr('map.prop2','value2');
```

