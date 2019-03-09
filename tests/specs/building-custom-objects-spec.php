<?php

namespace Custom_Object_Builders_Spec;

use Haijin\Object_Builder\Object_Builder;

$spec->describe( "When building custom objects", function() {

    $this->let( "object_builder", function() {

        return new Object_Builder();

    });

    $this->it( "builds the object with an Object_Builder instance", function() {

        $source = [ "Lisa", "Simpson", [ "Evergreen", "742" ] ];

        $object = $this->object_builder->build( $source, function($obj, $source) {

            $obj->set_to( new \stdclass() );

            $obj->name = "Lisa";
            $obj->last_name = "Simpson";

            $obj->address = $obj->build( $source[2], new Address_Builder_Callable() );
        });

        $this->expect( $object ) ->to() ->be() ->exactly_like([
            "name" => "Lisa",
            "last_name" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);

    });

    $this->it( "builds the object with an Object_Builder class", function() {

        $source = [ "Lisa", "Simpson", [ "Evergreen", "742" ] ];

        $object = $this->object_builder->build( $source, function($obj, $source) {

            $obj->set_to( new \stdclass() );

            $obj->name = "Lisa";

            $obj->last_name = "Simpson";

            $obj->address = $obj->build( $source[2], Address_Builder_Callable::class );

        });

        $this->expect( $object ) ->to() ->be() ->exactly_like([
            "name" => "Lisa",
            "last_name" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);

    });

    $this->it( "builds the object with an Object_Builder converter method class", function() {

        $source = [ "Lisa", "Simpson", [ "Evergreen", "742" ] ];

        $custom_object_builder = new Custom_Object_Builder();

        $object = $custom_object_builder->build( $source, function($obj, $source) {

            $obj->set_to( new \stdclass() );

            $obj->name = "Lisa";

            $obj->last_name = "Simpson";

            $obj->address = $obj->to_address( $source[2] );

        });

        $this->expect( $object ) ->to() ->be() ->exactly_like([
            "name" => "Lisa",
            "last_name" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);

    });

});

class Address_Builder_Callable
{
    public function __invoke($address, $source)
    {
        $address->set_to( new \stdclass() );

        $address->street = $source[ 0 ] . " " . $source[ 1 ];
    }
}

class Address_Builder
{
    public function __invoke($address, $source)
    {
        $address->set_to( new \stdclass() );

        $address->street = $source[ 0 ] . " " . $source[ 1 ];
    }
}

class Custom_Object_Builder extends Object_Builder
{
    public function to_address( $source )
    {
        return $this->build( $source, function($address, $source) {

            $address->set_to( new \stdclass() );

            $address->street = $source[ 0 ] . " " . $source[ 1 ];

        });
    }
}