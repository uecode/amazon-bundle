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
	"uecode/amazon-bundle": "dev-master",
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
                #custom config file is optional see http://docs.aws.amazon.com/aws-sdk-php/guide/latest/credentials.html for other configuration options    
                custom_config_file: '%kernel.root_dir%/config/aws.json'
                log_adapter: 'MonologLogAdapter'
	```

4. yml config for SWF

    ```yml
        uecode:
            amazon:
                #custom config file is optional see http://docs.aws.amazon.com/aws-sdk-php/guide/latest/credentials.html for other configuration options
                custom_config_file: '%kernel.root_dir%/config/aws.json'
                log_adapter: 'MonologLogAdapter'
                simpleworkflow:
                    domains:
                        uePoc:
                            description: "UE's domain for SWF proof of concept."
                            workflow_execution_retention_period: 8
                            workflows:
                                - name: leads
                                  version: 1.9
                                  class: Ue\AmazonBundle\Component\SimpleWorkFlow\v1_9\Decider
                                  default_child_policy: REQUEST_CANCEL
                                  default_task_list: spool
                                  default_task_timeout: 600
                                  default_execution_timeout: 31536000
                            activities:
                                - name: InsertLead
                                  version: 1.9
                                  class: Ue\AmazonBundle\Component\SimpleWorkFlow\v1_9\Activity\InsertLead
                                  default_task_list: spool
    ```

## Usage

In your code, after doing the above, you should be able to get an amazon service with:

```php
// get container
$service = $container->get('uecode.amazon');
```

```php
// Example to get a particular AWS object
// * AmazonClass - A wrapper for an Amazon service which would be located in Component/.
// * connection config key - A config value relative to
//   uecode.amazon.accounts.connections (e.g., "main").
$obj = $service->getAmazonService('AmazonClass', '<connection config key>', array(<service options>));
```

```php
// At present, this lib only has support for Amazon SWF.
$swf = $service->get('uecode.amazon')
 ->getAmazonService('SimpleWorkflow', '<connection config key>', array(<service options>));
```

This project is still in the making as are its docs but we should have some docs on
creating SWF workflows soon.


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/uecode/amazon-bundle/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

