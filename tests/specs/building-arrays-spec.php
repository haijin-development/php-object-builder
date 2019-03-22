<?php

use Haijin\Object_Builder\Object_Builder;
use Haijin\Errors\Haijin_Error;

$spec->describe( "When building arrays", function() {

    $this->let( "object_builder", function() {

        return new Object_Builder();

    });

    $this->it( "builds an empty array", function() {

        $array = $this->object_builder->build( function($array) {

            $array->set_to( [] );

        });

        $this->expect( $array ) ->to() ->equal( [] );

    });

    $this->it( "adds items to the array", function() {

        $array = $this->object_builder->build( function($array) {

            $array->set_to( [] );

            $array[] = "Lisa";

            $array[] = "Simpson";

        });

        $this->expect( $array ) ->to() ->be() ->exactly_like([
            "Lisa",
            "Simpson"
        ]);

    });

    $this->it( "builds nested arrays", function() {

        $array = $this->object_builder->build( function($array) {

            $array->set_to( [] );

            $array[] = "Lisa";

            $array[] = "Simpson";

            $array[] = $array->build( function($array) {

                $array->set_to( [] );

                $array[] = "Evergreen";

                $array[] = "742";

            });

        });

        $this->expect( $array ) ->to() ->be() ->exactly_like([
            "Lisa",
            "Simpson",
            [ 
                "Evergreen", "742"
            ] 
        ]);

    });

    $this->it( "builds arrays from a source object", function() {

        $user = [
            "name" => "Lisa",
            "last_name" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ];

        $array = $this->object_builder->build( $user, function($array, $user) {

            $array->set_to( [] );

            $array[] = $user[ "name" ];

            $array[] = $user[ "last_name" ];

            $array[] = $array->build( $user[ "address" ],
                                                function($array, $address) {

                $array->set_to( [] );

                $parts = explode(" ", $address[ "street" ]);

                $array[] = $parts[ 0 ];

                $array[] = $parts[ 1 ];

            });

        });

        $this->expect( $array ) ->to() ->be() ->exactly_like([
            "Lisa",
            "Simpson",
            [ 
                "Evergreen", "742"
            ] 
        ]);

    });

    $this->describe( 'when using the ArrayAccess protocol', function() {

        $this->it( 'set a value', function() {

            $array = $this->object_builder->build( null, function($array, $user) {

                $array->set_to( [] );

                $array[ 'user' ] = 'Lisa';

            });

            $this->expect( $array[ 'user' ] ) ->to() ->equal( 'Lisa' );

        });

        $this->it( 'gets a value', function() {

            $array = $this->object_builder->build( null, function($array, $user) {

                $array->set_to( [] );

                $array[ 'user' ] = 'Lisa';

                $this->expect( $array[ 'user' ] ) ->to() ->equal( 'Lisa' );

            });

        });

        $this->it( 'unsets a value and test for presence', function() {

            $array = $this->object_builder->build( null, function($array, $user) {

                $array->set_to( [] );

                $array[ 'user' ] = 'Lisa';

                unset( $array[ 'user' ] );

                $this->expect( isset( $array[ 'user' ] ) ) ->to() ->be() ->false();

            });

        });

    });

});