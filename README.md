# Haijin Object_Builder

One direction serializers of complex objects using a simple DSL.

[![Latest Stable Version](https://poser.pugx.org/haijin/object-builder/version)](https://packagist.org/packages/haijin/object-builder)
[![Latest Unstable Version](https://poser.pugx.org/haijin/object-builder/v/unstable)](https://packagist.org/packages/haijin/object-builder)
[![Build Status](https://travis-ci.org/haijin-development/php-object-builder.svg?branch=v0.1.0)](https://travis-ci.org/haijin-development/php-object-builder)
[![License](https://poser.pugx.org/haijin/object-builder/license)](https://packagist.org/packages/haijin/object-builder)

**Highlights**

* Zero configuration needed and conventions assumed. No naming conventions required. 
* Json generation for an endpoint can be easily organized into many methods.
* All json generation methods for each endpoint can reside in a single endpoint file.
* Json generation methods can use native PHP conditionals and loops.
* Type convertion methods can easily be defined and used as regular PHP class methods.
* The mapping from the model to json does not need to be one to one. Some parts of the json response can be built from multiple models or from none without having to create new model classes.

### Version 2.0.0

If you like it a lot you may contribute by [financing](https://github.com/haijin-development/support-haijin-development) its development.

## Table of contents

1. [Installation](#c-1)
2. [Object_Builders](#c-2)
    1. [Json_Builder](#c-2-1)
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
## Object_Builders

An Object_Builder is an object that serializes complex nested objects using simple DSL instead of configurations files or classes.

The serialization is one way only because it usually involves loss of information. Consider the case where an application returns objects modeled in a database to a json API response. Not all the fields in the model will be included in the response. Internal id fields will be skipped. Other fields may depend on the role of the user making the request. Some value types may be converted, etc.

On the other hand, some fields will be added to the response that may not be part of the model, such as the API version number, pagination information or timestamps related to the request, not the model.


<a name="c-2-1"></a>
### Json_Builder

An Object_Builder subclass that builds a JSON.

<a name="c-2-1-1"></a>
#### Standalone example

Serialize a User object to a Json associative array:

```php
use Haijin\Object_Builder\Json_Builder;

$json_builder = new Json_Builder();

$json_object = $json_builder->build( $user, function($json, $user) {

    $json->name = $user->get_name();

    $json->last_name = $user->get_last_name();

    $json->address = $json->build( $user->get_address(), function($json, $address) {

        $json->street = $address->get_street_name();

        $json->number = $json->to_int( $address->get_street_number() );

    });

});
```

**Example remarks**.

Sets a value to a field:

```php
$json->name = $user->get_name();
```

Uses a closure to create a new json object from an Address model and assigns it to the address field:

```php
$json->address = $json->build( $user->get_address(), function($json, $address) {
    // ...
});
```

This line is an expressive way of converting a value to an int:

```php
$json->number = $this->to_int( $address->get_street_number() );
```

It's optional, a simple:

```php
$json->number = (int) $address->get_street_name();
```

produces the same result, but with more complex custom convertions that form gains in expressiveness.

<a name="c-2-1-2"></a>
#### Integrating it to an endpoint class

Given the model classes

```php
class AddressSample
{
    public function get_street()
    {
        return "Evergreen";
    }

    public function get_number()
    {
        return "742";
    }
}

class SampleUser
{
    public function get_name()
    {
        return "Lisa";
    }

    public function get_last_name()
    {
        return "Simpson";
    }

    public function get_addresses()
    {
        return [ new AddressSample() ];
    }
}
```

And an Endpoint class that serializes a SampleUser object to produce the response

```php
[
    "response" => [
        "api_version" => "1.0.0",
        "success" => true,
        "data" => [
            "user" => [
                "name" => "Lisa",
                "last_name" => "Simpson",
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

integrate the Json_Builder and factorize its building block like this:

```php
use Haijin\Object_Builder\Json_Builder;

class Endpoint
{
    public function handle_request()
    {
        $user = new User();

        return $this->build_json_from( $user );
    }

    protected function build_json_from($user)
    {
        $json_builder = new Json_Builder();

        return $json_builder->build( $user, function($json, $user) {

            if( $this->successful_request() ) {

                $json->response = 
                    $this->success_response_to_json( $json, $user );

            } else {

                $json->response = 
                    $this->failed_response_to_json( $json, $this->get_errors() );
            }

            $json->response->api_version = "1.0.0";

        });
    }

    protected function success_response_to_json($json, $user)
    {
        return $json->build( $user, function($json, $user) {

            $json->success = true;

            $json->data = [
                "user" => $this->user_to_json( $json, $user )
            ];
        }) ;
    }

    protected function user_to_json($json, $user)
    {
        return $json->build( $user, function($json, $user) {

            $json->name = $user->get_name();

            $json->last_name = $user->get_last_name();

            $json->addresses = [];

            foreach( $user->get_addresses() as $address ) {
                $json->addresses[] = $this->address_to_json( $json, $address );
            }

        });
    }

    protected function address_to_json($json, $address)
    {
        return $json->build( $address, function($json, $address) {

            $json->street = $json->to_string( $address->get_street() );

            $json->number = $json->to_int( $address->get_number() );
        });
    }

    protected function failed_response_to_json($json, $errors)
    {
        return $json->build( $user, function($json, $user) {

            $json->success = false;

            $json->errors = $json->build( $errors, Response_Errors_Builder::class );

        }) ;
    }
}
```

<a name="c-2-2"></a>
### Building objects

Build objects of any class by defining the appropiate `target` and using its own protocol:

```php
use Haijin\Object_Builder\Json_Builder;

$object_builder = new Object_Builder();

$user = $object_builder->build( function($obj) {

    $obj->set_to( new User() );

    $obj->set_name( "Lisa" );

    $obj->set_last_name( "Simpson" );

    $obj->set_address(

        $this->build( function($obj) {

            $obj->set_to( new Address() );
            
            $obj->set_street( "Evergreen" );

            $obj->set_number( 742 );

        });

    );

});
```
**Remarks**

This first line of each building closure creates the object, array or value which is later populated:

```php
$obj->set_to( new User() );
$obj->set_to( [] );
```

<a name="c-2-3"></a>
### Reusing builders

Reuse common builders into callable classes:

```php
class Address_Builder
{
    public function __invoke($address)
    {
        $address->set_to( [] );

        $address->street =
            $address->get_street_name() . " " . $address->get_street_number();
    }
}
```

and use it:

```php
$object_builder = new Object_Builder();

$object = $object_builder->build( $user, function($obj, $user) {

    $obj->set_to( [] );

    $obj->name = $user->get_name();

    $obj->last_name = $user->get_last_name();

    $obj->address = $this->build_with( $user->get_address(), new Address_Builder() );

});
```

or 

```php
$object_builder = new Object_Builder();

$object = $object_builder->build( $user, function($obj, $user) {

    $obj->set_to( [] );

    $obj->name = $user->get_name();

    $obj->last_name = $user->get_last_name();

    $obj->address = $this->build_with( $user->get_address(), Address_Builder::class );

});
```


<a name="c-3"></a>
## Running the tests

```
composer specs
```