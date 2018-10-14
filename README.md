CraftCamp ABAC Bundle
=================

[![Latest Stable Version](https://poser.pugx.org/craftcamp/abac-bundle/v/stable)](https://packagist.org/packages/craftcamp/abac-bundle)
[![Latest Unstable Version](https://poser.pugx.org/craftcamp/abac-bundle/v/unstable)](https://packagist.org/packages/craftcamp/abac-bundle)
[![Build Status](https://travis-ci.org/CraftCamp/abac-bundle.svg?branch=master)](https://travis-ci.org/CraftCamp/abac-bundle)
[![Code Coverage](https://scrutinizer-ci.com/g/CraftCamp/abac-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/CraftCamp/abac-bundle/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/CraftCamp/abac-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/CraftCamp/abac-bundle/?branch=master)
[![Total Downloads](https://poser.pugx.org/craftcamp/abac-bundle/downloads)](https://packagist.org/packages/craftcamp/abac-bundle)
[![License](https://poser.pugx.org/craftcamp/abac-bundle/license)](https://packagist.org/packages/craftcamp/abac-bundle)

Introduction
------------

This Symfony bundle implements support in the Symfony framework for the [PHP ABAC library](https://github.com/CraftCamp/php-abac).

This is meant to implement in Symfony applications a new way to handle access control.

This method is based on a policy rules engine, analyzing user and resources attributes instead of roles alone.

Roles can be used, considering them as user attributes.

The advantages of this method is to easily define rules checking user and accessed resources attributes to handle access control.

```php
<?php

class MarketController extends Controller
{
    public function buyAction($productId) {
        $product = $this->get('product_manager')->getProduct($productId);
        // Call the "craftcamp_abac.security" to check if the user can buy the given product
        $access = $this->get('craftcamp_abac.security')->enforce(
            'product_buying_rule', // the rule name
            $this->getUser(), // The current user
            $product // The resource we want to check for access
        );
        if($access !== true) {
            return new JsonResponse([
                // In case of denied access, the library will return an array of the unmatched attributes slugs
                'rejected_attributes' => $access
            ], 403);
        }
    }
}
```

Installation
------------

Use composer to set the bundle as your project dependency :

```
composer require craftcamp/abac-bundle
```

Then you must load the bundle in your AppKernel file and configure it :

```php
<?php
// app/AppKernel.php
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new CraftCamp\AbacBundle\CraftCampAbacBundle(),
        ];
        // ...
        return $bundles;
    }
}
```

```yaml
#app/config/config.yml
craftcamp_abac:
    configuration_files:
        - app/config/attributes.yml
        - app/config/policy_rules.yml
    cache_options: # optional
        cache_folder: '%kernel.cache_dir%/abac'
```

Documentation
-------------

Please refer to the [PHP ABAC documentation](https://github.com/CraftCamp/php-abac)

Usage
-----

This bundle creates a Symfony service with the php-abac main class.

To check if a rule is enforced, you must define a rule in your configuration file and then check it.

A rule can check user and resource attributes or just the user's.

This is an example of configured rule:

```yaml
# policy_rules.yml
# You can set the attributes and the rules definitions in the same file if you want
# Or in multiple files
---
attributes:
    main_user:
        class: PhpAbac\Example\User
        type: user
        fields:
            age:
                name: Age
            parentNationality:
                name: Parents nationality
            hasDrivingLicense:
                name: Driving License
            
    vehicle:
        class: PhpAbac\Example\Vehicle
        type: resource
        fields:
            origin:
                name: Origin
            owner.id:
                name: Owner
            manufactureDate:
                name: Release date
            lastTechnicalReviewDate:
                name: Last technical review
        
    environment:
        service_status:
            name: Service status
            variable_name: SERVICE_STATUS

rules:
    vehicle-homologation:
        attributes:
            main_user.hasDrivingLicense:
                comparison_type: boolean
                comparison: boolAnd
                value: true
            vehicle.lastTechnicalReviewDate:
                comparison_type: datetime
                comparison: isMoreRecentThan
                value: -2Y
            vehicle.manufactureDate:
                comparison_type: datetime
                comparison: isMoreRecentThan
                value: -25Y
            vehicle.owner.id:
                comparison_type: numeric
                comparison: isEqual
                value: dynamic
            vehicle.origin:
                comparison_type: array
                comparison: isIn
                value: ["FR", "DE", "IT", "L", "GB", "P", "ES", "NL", "B"]
            environment.service_status:
                comparison_type: string
                comparison: isEqual
                value: OPEN

```

And then in your controller :

```php
<?php

class VehicleHomologationController extends Controller
{
    public function homologateAction($vehicleId) {
        $vehicle = $this->get('vehicle_manager')->getProduct($vehicleId);
        // Call the "craftcamp_abac.security" to check if the user can homologate the given vehicle
        $access = $this->get('craftcamp_abac.security')->enforce(
            'vehicle-homologation', // the rule name
            $this->getUser(), // The current user
            $vehicle // The resource we want to check for access
        );
        if($access !== true) {
            return new JsonResponse([
                // In case of denied access, the library will return an array of the unmatched attributes slugs
                'rejected_attributes' => $access
            ], 403);
        }
    }
}
```

Since 0.3.0, you can use autowiring in your controller

```php
<?php

use PhpAbac\Abac;

class VehicleHomologationController extends Controller
{
    public function homologateAction(Abac $abac, $vehicleId) {
        $vehicle = $this->get('vehicle_manager')->getProduct($vehicleId);

        $access = $abac->enforce(
            'vehicle-homologation', // the rule name
            $this->getUser(), // The current user
            $vehicle // The resource we want to check for access
        );
        if($access !== true) {
            return new JsonResponse([
                // In case of denied access, the library will return an array of the unmatched attributes slugs
                'rejected_attributes' => $access
            ], 403);
        }
    }
}
```

Overiding components
--------------------

The ``Abac`` service being autowired, you can replace any of its dependencies by reconfiguring their aliases.

For instance, if you want to implement your own ``CacheManager``, you just have to implement the following configuration:

```
# services.yaml
services:
    App\Cache\MyCacheManager:
        public: true
        autowire: true

    PhpAbac\Manager\CacheManagerInterface: '@App\Cache\MyCacheManager'
```

Of course your component must implement the associated interface.

The overridable interfaces are:

* PhpAbac\Configuration\ConfigurationInterface
* PhpAbac\Manager\PolicyRuleManagerInterface
* PhpAbac\Manager\AttributeManagerInterface
* PhpAbac\Manager\ComparisonManagerInterface
* PhpAbac\Manager\CacheManagerInterface