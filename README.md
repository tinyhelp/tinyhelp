# TinyHelp

> A dead simple library to inject module suggestions into WordPress plugin search results.

[![License](http://img.shields.io/:license-mit-blue.svg)](http://doge.mit-license.org)

Since apparently it is now okay to inject suggestions into WordPress plugin search results on wp-admin, this little library will help you to do just that. All you have to do is to include it in your project, define some suggestions in an array and pass it to `TinyHelp` class. Here is how it looks:

![Imgur](https://i.imgur.com/2wdnWy9.jpg)

## Usage

Here is some sample code:

```php
$args = array(
	'slug'        => 'tinycoffee2',
	'name'        => __( 'tinyCoffee', 'tinyhelp-sample' ),
	'bottom_text' => __( 'This is a feature suggestion created by tinyHelp library', 'tinyhelp-sample' ),
	'modules'     => array(
		'test'  => array(
			'name'              => __( 'Test Module', 'tinyhelp-sample' ),
			'short_description' => __( 'Short description for this injected module goes here', 'tinyhelp-sample' ),
			'version'           => '0.1.0',
			'author_name'       => __( 'Arūnas', 'tinyhelp-sample' ),
			'author_uri'        => 'https://arunas.co',
			'search_terms'      => array( 'test.*', 'backup', 'coffee' ),
			'icons'             => array(
				'1x'  => 'https://placeholder.pics/png/128',
				'2z'  => 'https://placeholder.pics/png/256',
				'svg' => 'https://placeholder.pics/svg/300',
			),
			'links'             => array(
				array(
					'type'       => 'button',
					'title'      => __( 'Get started', 'tinyhelp-sample' ),
					'link'       => 'https://wp.org/plugins/tinycoffee',
					'attributes' => array(
						'target' => '_blank',
					),
				),
				array(
					'type'       => 'link',
					'title'      => __( 'Learn More', 'tinyhelp-sample' ),
					'link'       => 'https://arunas.co',
					'attributes' => array(
						'target' => '_blank',
					),
				),
			),
		),
		'test2' => array(
			'name'              => __( 'Test Module 2', 'tinyhelp-sample' ),
			'short_description' => __( 'Short description for this injected module goes here', 'tinyhelp-sample' ),
			'version'           => '0.1.0',
			'author_name'       => __( 'Arūnas', 'tinyhelp-sample' ),
			'author_uri'        => 'https://arunas.co',
			'search_terms'      => array( 'testing', 'beta', 'backup' ),
			'icons'             => array(
				'1x'  => 'https://placeholder.pics/png/128',
				'2z'  => 'https://placeholder.pics/png/256',
				'svg' => 'https://placeholder.pics/svg/300',
			),
			'links'             => array(
				array(
					'type'       => 'button',
					'title'      => __( 'Get started', 'tinyhelp-sample' ),
					'link'       => 'https://arunas.blog',
					'attributes' => array(
						'target' => '_blank',
					),
				),
			),
		),
	),
);
$th = new TinyHelp( $args );
```

You can also check it out as a sample plugin <a href="https://github.com/tinyhelp/tinyhelp-sample">here</a> (you'll need to run `composer install` after you checkout the repo).

## License

This library is open source and free for everyone to use. It is licensed under MIT license.

## Author

This library was built by <a href="https://arunas.co">Arūnas Liuiza</a>, based on concept, first introduced by Jetpack. Any contributions are welcome.

## Notice

This is still a very fresh project. If you note any bugs, please report them and I'll try to fix them ASAP. I feel that the code is quite self explanatory, but I'll add more comments and documentation in the future, as well.
