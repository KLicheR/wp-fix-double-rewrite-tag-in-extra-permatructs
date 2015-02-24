Fix double rewrite tag in extra Permastructs
===============================================================================
This plugin fix a really specific case when you use the _rewrite tag_ of a custom taxonomy in the _rewrite slug_ of this same taxonomy. WordPress will always add the _rewrite tag_ at the end of the _rewrite slug_ so this plugin is useful to detect that you already use the _rewrite tag_ in the _rewrite slug_ and reconstruct _extra permastructs_ without the adding the _rewrite tag_ at the end.

Usage
-------------------------------------------------------------------------------
Add a filter to specified the taxonomies you want to fix.

~~~php
<?php
add_filter('taxonomies_extra_structure_to_fix', function($taxonomies_to_fix) {
    return array('color');
});
?>
~~~

Example
-------------------------------------------------------------------------------
### Custom taxonomy with the taxo _rewrite tag_ in the _rewrite slug_
~~~php
<?php
register_taxonomy('color',
    array('product'),
    array(
        'label' => 'Colors',
        'labels' => array(
            'name' => 'Colors',
            'singular_name' => 'Color',
            'menu_name' => 'Colors',
        ),
        'public' => true,
        'rewrite' => array(
            'slug' => 'my-product-color/%color%/products',
        ),
    )
);
?>
~~~

This will create URLs like:
- `/my-product-color/blue/products/blue`
- `/my-product-color/red/products/red`

The permastructs behind it will be: `/my-product-color/%color%/products/%color%`. Which is noooot cool.

With my plugin, you'll obtain these URLs:
- `/my-product-color/blue/products`
- `/my-product-color/red/products`

The permastructs behind it will be: `/my-product-color/%color%/products`.

WordPress behaviour
-------------------------------------------------------------------------------
If you're interested in seeing how WordPress do this, you can check the line with the method `add_permastruct` in the function `register_taxonomy` of the file `/wp-includes/taxonomy.php`.