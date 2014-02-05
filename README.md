Amazon Bundle
============

This bundle is a semantic configuration and service provider for the AWS PHP SDK v2

## General Installation

1. Add to composer.json under `require`

```
"uecode/amazon-bundle": ">=2.0.0, <3.0.0",
```

2. Register in `AppKernel`

``` php
$bundles = array(
	// ...
	new Uecode\Bundle\AmazonBundle\AmazonBundle()
);
```

3. Add Account info to your config.yml

```yml
uecode_amazon:
    accounts:
        main:
            key: somekey
            secret: somesecret
```

## Usage

In your code, after doing the above, you should be able to get an amazon service with:

```php
// get container
$service = $container->get('uecode_amazon.instance.main');
// OR
$service = $container->get('aws.main');
```

After getting the service, you will be able to fetch any of the services in the AWS service Locator.

For help there, follow these guides: [AWS SDK for PHP][0]. When following there guides, you won't need to use the factory classes,
you should just be able to run `service->get('service_name')`.

For Example

```php
$cloudFront = $container->get('aws.main')->get('CloudFront');
```

## Copyright

Copyright (c) 2014 Underground Elephant

## License

Licensed under the Apache License, Version 2.0.

See [LICENSE][1].


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/uecode/amazon-bundle/trend.png)](https://bitdeli.com/free "Bitdeli Badge")


[0]: http://docs.aws.amazon.com/aws-sdk-php/guide/latest/index.html#service-specific-guides
[1]: https://github.com/uecode/amazon-bundle/LICENSE
