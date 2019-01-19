# Haijin Object_Builder

One direction serializers of complex objects using a simple DSL.

[![Latest Stable Version](https://poser.pugx.org/haijin/object-builder/version)](https://packagist.org/packages/haijin/object-builder)
[![Latest Unstable Version](https://poser.pugx.org/haijin/object-builder/v/unstable)](https://packagist.org/packages/haijin/object-builder)
[![Build Status](https://travis-ci.org/haijin-development/php-object-builder.svg?branch=v0.1.0)](https://travis-ci.org/haijin-development/php-object-builder)
[![License](https://poser.pugx.org/haijin/object-builder/license)](https://packagist.org/packages/haijin/object-builder)

### Version 1.0.0

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
        "haijin/object-builder": "^1.0",
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

```php
$json_object = \Haijin\Object_Builder\Json_Builder::build_json( function($json) use($user) {
    $json->name = $user->get_name();

    $json->last_name = $user->get_last_name();

    $address = $user->get_address();
    $json->address = $this->build( function($json) use($address) {

        $json->street = $address->get_street_name();
        $json->number = $this->convert( $address->get_street_name() ) ->to_int();
    });
});
```

**Example remarks**.

Sets a value to a field:

```php
$json->name = $user->get_name();
// or
$json["name"] = $user->get_name();
```

Assigns the user address to a variable to be able to pass it along to the closure below it:

```php
$address = $user->get_address();
```

Uses a closure to create a new json object and assigns it to the address field:

```php
$json->address = $this->build( function($json) use($address) {
    // ...
});
```

This line is an expressive way of converting a value to an int:

```php
$json->number = $this->convert( $address->get_street_name() ) ->to_int();
```

It's optional, a simple:

```php
$json->number = (int) $address->get_street_name();
```

produces the same result, but with more complex custom convertions that form gains in expressiveness.

<a name="c-2-1-2"></a>
#### Integrating it to a class

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

And an Action class that serializes a SampleUser object to produce the response

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

class Action
{
    public function get_json()
    {
        // Get a sample model object.
        $user = new SampleUser();

        // Build the JSON response from the model object.
        return $this->build_json_from($user);
    }

    protected function build_json_from($user)
    {
        return Json_Builder::build_json( function($json) use($user) {

            $json->response = $this->success_response_to_json( $json, $user );

        }, $this);
    }

    protected function success_response_to_json($json, $user)
    {
        return $json->build( function($json) use ($user) {

            $json->api_version = "1.0.0";

            $json->success = true;

            $json->data = [
                "user" => $this->user_to_json( $json, $user )
            ];
        }) ;
    }

    protected function user_to_json($json, $user)
    {
        return $json->build( function($json) use($user) {

            $json->name = $user->get_name();

            $json->last_name = $user->get_last_name();

            $json->addresses = array_map(
                function($each_address) use($json) { return $this->address_to_json( $json, $each_address ); },
                $user->get_addresses()
            );
        });
    }

    protected function address_to_json($json, $address)
    {
        return $json->build( function($json) use($address) {

            $json->street = $json->convert( $address->get_street() ) ->to_string();

            $json->number = $json->convert( $address->get_number() ) ->to_int();
        });
    }
}
```

**Remarks**

In this method call, note the last parameter to the `build_json` call.

`$this` is passed as the last parameter to bind it to the `Action` object in all the closure evaluations.

If this last parameter was not passed, inside the closures `$this` would point to the Object_Builder object and not to the Action object, and calling other methods in the Action class would produce an error.

```php
protected function build_json_from($user)
{
    return Json_Builder::build_json( function($json) use($user) {

        $json->response = $this->success_response_to_json( $json, $user );

    }, $this);
}
```

<a name="c-2-2"></a>
### Building objects

Build objects of any class by defining the appropiate `target` and using its own protocol:

```php
$user = Object_Builder::build_object( function($obj) {
    $obj->target = new User();

    $obj->set_name( "Lisa" );
    $obj->set_last_name( "Simpson" );

    $obj->set_address(
        $this->build( function($obj) {
            $obj->target = new Address();
            
            $obj->set_street( "Evergreen" );
            $obj->set_number( 742 );
        }) 
);
```
**Remarks**

This first line of each building closure creates the array or object which is later populated:

```php
$obj->target = new User();
```

<a name="c-2-3"></a>
### Reusing builders

Reuse common builders into Object_Builder subclasses:

```php
class Address_Builder extends Object_Builder
{
    public function evaluate($address)
    {
        $this->target = [];

        $this->street = $address->get_street_name() . " " . $address->get_street_number();
    }
}
```

and use them:

```php
$object = Object_Builder::build_object( function($obj) use($user) {
    $obj->target = [];

    $obj->name = $user->get_name();
    $obj->last_name = $user->get_last_name();

    $obj->address = $this->build_with( new Address_Builder(), $user->get_address() );
});
```

or 

```php
$object = Object_Builder::build_object( function($obj) use($user) {
    $obj->target = [];

    $obj->name = $user->get_name();
    $obj->last_name = $user->get_last_name();

    $obj->address = $this->build_with( "Address_Builder", $user->get_address() );
});
```


<a name="c-3"></a>
## Running the tests

```
composer specs
```