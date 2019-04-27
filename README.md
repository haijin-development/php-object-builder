# Haijin ObjectBuilder

One direction serializers using a simple DSL.

[![Latest Stable Version](https://poser.pugx.org/haijin/object-builder/version)](https://packagist.org/packages/haijin/object-builder)
[![Latest Unstable Version](https://poser.pugx.org/haijin/object-builder/v/unstable)](https://packagist.org/packages/haijin/object-builder)
[![Build Status](https://travis-ci.org/haijin-development/php-object-builder.svg?branch=v0.1.0)](https://travis-ci.org/haijin-development/php-object-builder)
[![License](https://poser.pugx.org/haijin/object-builder/license)](https://packagist.org/packages/haijin/object-builder)

### Version 2.0.0

If you like it a lot you may contribute by [financing](https://github.com/haijin-development/support-haijin-development) its development.

## Table of contents

1. [Installation](#c-1)
2. [ObjectBuilders](#c-2)
    1. [JsonBuilder](#c-2-1)
        1. [Standalone example](#c-2-1-1)
        2. [Integrating it to a class](#c-2-1-2)
    2. [Building objects](#c-2-2)
    3. [Reusing builders](#c-2-3)
3. [Running the tests](#c-3)

<a name="c-1"></a>
## Installation

Include this library in your project `composer.json` file:

```json
{
    ...

    "require": {
        ...
        "haijin/object-builder": "^2.0",
        ...
    },

    ...
}
```

<a name="c-2"></a>
## ObjectBuilders

An ObjectBuilder is an object that serializes complex nested objects using a simple DSL.

The serialization is one way only because it usually involves loss of information. Consider the case where an application returns objects modeled in a database to a json API response. Not all the fields in the model will be included in the response. Internal id fields will be skipped. Other fields may depend on the role of the user making the request. Some value types may be converted, etc.

On the other hand, some fields will be added to the response that may not be part of the model, such as the API version number, pagination information or timestamps related to the request, not the model.


<a name="c-2-1"></a>
### JsonBuilder

An ObjectBuilder subclass that builds a JSON.

<a name="c-2-1-1"></a>
#### Standalone example

Serialize a User object to a Json associative array:

```php
use Haijin\ObjectBuilder\JsonBuilder;

$jsonBuilder = new JsonBuilder();

$jsonObject = $jsonBuilder->build( $user, function($json, $user) {

    $json->name = $user->getName();

    $json->lastName = $user->getLastName();

    $json->address = $json->build( $user->getAddress(), function($json, $address) {

        $json->street = $address->getStreetName();

        $json->number = $json->toInt( $address->getStreetNumber() );

    });

});
```

**Example remarks**.

Sets a value to a json field:

```php
$json->name = $user->getName();
```

Uses a closure to create a new json object from an Address model and assigns it to the address field:

```php
$json->address = $json->build( $user->getAddress(), function($json, $address) {
    // ...
});
```

This line is an expressive way of converting a value to an int:

```php
$json->number = $this->toInt( $address->getStreetNumber() );
```

It's optional, a simple:

```php
$json->number = (int) $address->getStreetName();
```

produces the same result, but with more complex custom conversions that form gains in expressiveness.

<a name="c-2-1-2"></a>
#### Integrating it to an endpoint class

Given the model classes

```php
class AddressSample
{
    public function getStreet()
    {
        return "Evergreen";
    }

    public function getNumber()
    {
        return "742";
    }
}

class SampleUser
{
    public function getName()
    {
        return "Lisa";
    }

    public function getLastName()
    {
        return "Simpson";
    }

    public function getAddresses()
    {
        return [ new AddressSample() ];
    }
}
```

And an Endpoint class that serializes a SampleUser object to produce the response

```php
[
    "response" => [
        "apiVersion" => "1.0.0",
        "success" => true,
        "data" => [
            "user" => [
                "name" => "Lisa",
                "lastName" => "Simpson",
                "addresses" => [
                    [
                        "street" => 'Evergreen',
                        "number" => 742
                    ]
                ]
            ]
        ]
    ]
]
```

integrate the JsonBuilder and factorize its building block like this:

```php
use Haijin\ObjectBuilder\JsonBuilder;

class Endpoint
{
    public function handleRequest()
    {
        $user = new User();

        return $this->buildJsonFrom($user);
    }

    protected function buildJsonFrom($user)
    {
        $jsonBuilder = new JsonBuilder();

        return $jsonBuilder->build( $user, function($json, $user) {

            if( $this->successfulRequest() ) {

                $json->response = 
                    $this->successResponseToJson( $json, $user );

            } else {

                $json->response = 
                    $this->failedResponseToJson( $json, $this->getErrors() );
            }

            $json->response->apiVersion = "1.0.0";

        });
    }

    protected function successResponseToJson($json, $user)
    {
        return $json->build( $user, function($json, $user) {

            $json->success = true;

            $json->data = [
                "user" => $this->userToJson( $json, $user )
            ];
        }) ;
    }

    protected function userToJson($json, $user)
    {
        return $json->build( $user, function($json, $user) {

            $json->name = $user->getName();

            $json->lastName = $user->getLastName();

            $json->addresses = [];

            foreach( $user->getAddresses() as $address ) {
                $json->addresses[] = $this->addressToJson( $json, $address );
            }

        });
    }

    protected function addressToJson($json, $address)
    {
        return $json->build( $address, function($json, $address) {

            $json->street = $json->toString( $address->getStreet() );

            $json->number = $json->toInt( $address->getNumber() );
        });
    }

    protected function failedResponseToJson($json, $errors)
    {
        return $json->build( $user, function($json, $user) {

            $json->success = false;

            $json->errors = $json->build( $errors, ResponseErrorsBuilder::class );

        }) ;
    }
}
```

<a name="c-2-2"></a>
### Building objects

Build objects of any class defining the proper `target` and using the object protocol:

```php
use Haijin\ObjectBuilder\ObjectBuilder;

$objectBuilder = new ObjectBuilder();

$user = $objectBuilder->build( function($obj) {

    $obj->setTo( new User() );

    $obj->setName( "Lisa" );

    $obj->setLastName( "Simpson" );

    $obj->setAddress(

        $this->build( function($obj) {

            $obj->setTo( new Address() );
            
            $obj->setStreet( "Evergreen" );

            $obj->setNumber( 742 );

        });

    );

});
```
**Remarks**

This first line of each building closure creates the object or array that is later populated:

```php
$obj->setTo(new User());
$obj->setTo([]);
```

<a name="c-2-3"></a>
### Reusing builders

Reuse common builders into callable classes:

```php
class AddressBuilder
{
    public function __invoke($address)
    {
        $address->setTo( [] );

        $address->street =
            $address->getStreetName() . " " . $address->getStreetNumber();
    }
}
```

and use it:

```php
$objectBuilder = new ObjectBuilder();

$object = $objectBuilder->build( $user, function($obj, $user) {

    $obj->setTo([]);

    $obj->name = $user->getName();

    $obj->lastName = $user->getLastName();

    $obj->address = $this->buildWith( $user->getAddress(), new AddressBuilder() );

});
```

or 

```php
$objectBuilder = new ObjectBuilder();

$object = $objectBuilder->build( $user, function($obj, $user) {

    $obj->setTo([]);

    $obj->name = $user->getName();

    $obj->lastName = $user->getLastName();

    $obj->address = $this->buildWith( $user->getAddress(), AddressBuilder::class );

});
```


<a name="c-3"></a>
## Running the tests

```
composer specs
```

Or if you want to run the tests using a Docker image with PHP 7.2:

```
sudo docker run -ti -v $(pwd):/home/php-object-builder --rm --name php-object-builder haijin/php-dev:7.2 bash
cd /home/php-object-builder/
composer install
composer specs
```