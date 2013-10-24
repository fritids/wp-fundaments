# Fundaments for WordPress

Fundaments is a framework for WordPress plugins. You install it just like any other plugin; it doesn't provide any
functionality out-of-the-box, but enables you to create plugins more quickly, by rapidly generating content types with
custom fields and taxonomies, and making settings pages quick and easy to manage.

## Getting started

First things first: [Download the plugin](https://github.com/substrakt/wp-fundaments/archive/master.zip)
and upload it to your wp-content/plugins directory, then activate it through the WordPress dashboard.

1. Create your new plugin directory, and inside it create a file called bootstrap.php
2. Create a function that will call `skt_register_plugin` with the directory name of your plugin
3. Attach that function to the `skt_bootstrap` action

An example bootstrap script is:

```pip
function myplugin_bootstrap() {
	skt_register_plugin(dirname(__file__));
}

add_action('skt_bootstrap', 'myplugin_bootstrap');
```

## Creating post types

Custom post types are all declared using classes, named a specific way and put in a particular directory. To create a
custom post type:

1. Create a directory inside your plugin folder called `post_types`
2. Create a PHP file, where the name of the file is the name of your custom post type (ie: document.php)
3. Create a class whose name is derived from your filename (like `DocumentPostType`)
4. Make the class inherit from `SktPostType`

Here's an example:

```php
class DocumentPostType extends SktPostType {
	...
}
```

It's actually that simple. Your post type will be created, using the above naming convention. You can have a post type with
multiple words in its name too. Let's say your type was called `magazine_article`. You'd create magazine_article.php and then call
your class `MagazineArticlePostType`. The framework would then create a custom post type whose human-readable name would be
"magazine article".

TODO: Document the optional properties of the `SktPostType` base class

