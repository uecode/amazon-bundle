Amazon Bundle
============

This bundle handles connections w/ various Amazon AWS services.

## Copyright

Copyright (c) 2013 Underground Elephant

## License

Licensed under the Apache License, Version 2.0.

See LICENSE-2.0.txt.

## General Installation

1. Add to composer.json under `require`

	```
	"uecode/amazoa-bundle": "dev-master",
	```

2. Register in `AppKernel`

	``` php
		$bundles = array(
		// ...
		new Uecode\Bundle\UecodeBundle\UecodeBundle()
		new Uecode\Bundle\AmazonBundle\AmazonBundle()
	```

3. Add Account info to your config.yml

	```yml
	uecode:
	    amazon:
	        accounts:
	            connections:
	                main:
	                    key: somekey
	                    secret: somesecret
	```

## Usage

In your code, after doing the above, you should be able to get the amazon factory with:

```php
$amazonFactory = $container->get( 'uecode.amazon' )->getFactory('ue');

// Example to get a particular AWS object
$obj = $amazonFactory->build('AmazonClass', array(), $container);
```

At present, this lib only has support for Amazon SWF.
```
$swf = $amazonFactory->build( 'AmazonSWF', array(), $container);
```

This project is still in the making as are its docs but we should have some docs on
creating SWF workflows soon.
