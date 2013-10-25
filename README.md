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

### Optional properties

- `$rewrite`: Add a custom URL pattern, like `/my-custom-plugin/%slug%`. Use in conjunction with the `permalink()` function.
- `$fields` - An array of custom fields that are automatically added to the Edit Post page of your custom post type.
- `$meta_boxes` - An array of custom meta boxes, allowing more granular control over the UI and placement of custom fields.
- `$list_fields` - An array of field names that should be displayed as columns in the post type's list page.
- `$label` - The human-readable name of your post type (by default, derived from the class name).
- `$name` - The pluralised name of the post type (by default, created by pluralising the name derived from the class name).
- `$singular_name` - The singular name of the post type (by default, derived from the class name).
- `$add_new_item`, `$edit_item`, `$new_item`, `$view_item`, `$search_items`, `$not_found`, `$not_found_in_trash` - 
 The various labels that would be passed into the `labels` argument of the `register_post_type` function. These are automatically
 calculated and passed to that function.
- `$description` - The post type description
- `$public` - Whether to make the post type public, create a UI and make it queryable (defaults to `true`)
- `$queryable` - Whether to make the post type publicly queryable (defaults to the value of `$public`)
- `$menu_position` - A number denoting the position on the WordPress dashboard menu for this type
- `$capability_type` - The default capability of this type (defaults to `page`)
- `$supports` - An array with the fields and functionality the type supports (defaults to `title`, `slug`, `editor` and `thumbnail`)
- `$hierarchical` - Whether the post type is hierarchical (defaults to `true`)
- `$parent` - the parent post type. If set, the link to the post type can be found in the menu of its parent type

### Custom fields

Custom fields are defined like this:

```php
$fields = array(
	'myfield' => array(
		'type' => 'text',
		'class' => 'widefat',
		'placeholder' => 'My placeholder text'
	)
);
```

You can use HTML5 input types like `text`, `number` or `email`, `select`, `radio` and `checkbox`, and get more advanced via
`post` or `post:[post-type]`, which automatically renders a radio-select or traditional `select` control allowing admins to pick
a related post of a given type (where `[post-type]` is the name of the type).

That field will automatically be rendered and saved by the framework, and is accessible in a loop via the
`skt_get_field` function (just by passing in the name of the field).

Any custom fields specified here and not mentioned in `$meta_boxes` will be automatically added to the Edit Post page.

If you add a field name to the `$list_fields` array, it will be rendered in post list table. If your field uses a post type as
its `type` property, a link to that post will be shown in that column.

Field labels are rendered in various places. They're taken from the field name, by replacing underscores with spaces and running
the `ucwords` function over the resulting string.

You don't need to worry about creating namespaces for your field names. Fundaments takes care of this by creating a field name
derived from your plugin name, the post type and the field name. Custom field data is stored in the standard way, as post metadata.
Array data is serialised and saved to a single field. If you blank out a field, its reference will be removed from the database.

### Meta boxes

Meta boxes (extra panels that display within the Edit Post page of your custom post type) are defined like so:

// Add custom meta-boxes that show the fields we've defined above
```php
public $meta_boxes = array(
	'mybox' => array(
		'title' => 'My Custom Field Box',
		'context' => 'side',
		'priority' => 'core',
		'fields' => array('myfield')
	)
);
```

The `fields` key is optional. Omitting this will instruct the framework to render a standard view, whose template you need to create
in your plugin directory, under the folder "views/post_types/mytype/meta/mymbox.php" (where "mytype" is the name of the custom
post type and "mybox" is the name of the meta-box).

You'll be responsible for handling any POST data from fields you manually create using this method. You can handle that data by
creating a function in your custom post type class called either `pre_save` or `post_save`, which accept a `$post_id`` argument.

### Built-in functions

There are a number of built-in functions you can leverage to save time and deal with things like custom fields with more
granularity.

#### `get_field($post, $field, $default)`

The `get_field` function returns the data stored in a field. The `$post` and `$field` arguments are required. The first argument
should be set to a post ID or `WP_Post` object, the second should be the name of your field. You can optionally set a default
value in the third argument, which should be returned when no data is available.

If your custom field points to a post type, the actual post will be returned, as a `WP_Post` object.

#### `set_field($post, $field, $value)`

The `set_field` function sets the value for a field. The `$post` and `$field` arguments are required. The first argument
should be set to a post ID or `WP_Post` object, the second should be the name of your field. If your `$value` object contains an
object of type `WP_Post`, the ID will be stored in the database but the `get_field` function will return the given post, as long
as your field is defined using the correct `type` option (See the "Custom fields" section).

#### `delete_field($post, $field)`

The `delete_field` function clears the value for a field. Both arguments are required. The first should be set to a post ID or
`WP_Post` object, the second should be the name of your field.

### Extend your post type

You can define a number of methods within your class to add extra functionality to your post type.

#### `the_content`

Rather than use the `the_content` hook, you can define a function within your class called `the_content` which accepts two arguments,
the `$post_id` and the `$content`. Return your altered content string and you're done. That function will only ever run for your
given post type, so there's no need to detect it, and it's already hooked up for you.

#### `pre_save`

Create a function in your class called `pre_save()` which accepts a `$post_id` argument, and that function will run before any
custom post type data is saved.

#### `post_save`

Create a function in your class called `post_save()` which accepts a `$post_id` argument, and that function will run after any
custom post type data is saved.

#### `save_field_{fieldname}`

Create a function in your class called `save_field_{fieldname}`, where `{fieldname}` is the name of one of your custom fields, to
handle the saving of that field yourself. The function needs to accept a `$post_id` and a `$value` argument.