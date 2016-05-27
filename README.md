Kilix ABAC Bundle
=================

[![Latest Stable Version](https://poser.pugx.org/kilix/abac-bundle/v/stable)](https://packagist.org/packages/kilix/abac-bundle)
[![Latest Unstable Version](https://poser.pugx.org/kilix/abac-bundle/v/unstable)](https://packagist.org/packages/kilix/abac-bundle)
[![Build Status](https://travis-ci.org/Kilix/abac-bundle.svg?branch=master)](https://travis-ci.org/Kilix/abac-bundle)
[![Code Coverage](https://scrutinizer-ci.com/g/Kilix/abac-bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Kilix/abac-bundle/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Kilix/abac-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Kilix/abac-bundle/?branch=master)
[![Total Downloads](https://poser.pugx.org/kilix/abac-bundle/downloads)](https://packagist.org/packages/kilix/abac-bundle)
[![License](https://poser.pugx.org/kilix/abac-bundle/license)](https://packagist.org/packages/kilix/abac-bundle)

Introduction
------------

This Symfony bundle implements support in the Symfony framework for the [PHP ABAC library](https://github.com/Kilix/abac-bundle).

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
        // Call the "kilix_abac.security" to check if the user can buy the given product
        $access = $this->get('kilix_abac.security')->enforce(
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
composer require kilix/abac-bundle
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
            new Kilix\AbacBundle\KilixAbacBundle(),
        ];
        // ...
        return $bundles;
    }
}
```

```yaml
#app/config/config.yml
kilix_abac:
    configuration_files:
        - app/config/attributes.yml
        - app/config/policy_rules.yml
```

Documentation
-------------

Please refer to the [PHP ABAC documentation](https://github.com/Kilix/abac-bundle)

Usage
-----

This bundle creates a Symfony service with the php-abac main class.

To check if a rule is enforced, you must define a rule in your configuration file and then check it.

A rule can check user and resource attributes or just the user's.

This is an exmaple of configured rule:

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
        // Call the "kilix_abac.security" to check if the user can homologate the given vehicle
        $access = $this->get('kilix_abac.security')->enforce(
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
