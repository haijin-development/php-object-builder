<?php

use Haijin\Object_Builder\Object_Builder;
use Haijin\Errors\Haijin_Error;

$spec->describe( "When validating the Object_Builder", function() {

    $this->let( "object_builder", function() {

        return new Object_Builder();

    });

    $this->it( "validates that there is a target object before setting a value", function() {

        $this->expect( function() {

            $this->object_builder->build( function($array) {
                $array[ 'name' ] = "Lisa";
            });

        }) ->to() ->raise(
            Haijin_Error::class,
            function($error) {
                $this->expect( $error->getMessage() ) ->to() ->equal(
                    "A target object must be set first with \$target->set_to( new Object() );"
                );
            }
        );

    });

    $this->it( "validates that there is a target object before getting a value", function() {

        $this->expect( function() {

            $this->object_builder->build( function($array) {
                $array[ 'name' ];
            });

        }) ->to() ->raise(
            Haijin_Error::class,
            function($error) {
                $this->expect( $error->getMessage() ) ->to() ->equal(
                    "A target object must be set first with \$target->set_to( new Object() );"
                );
            }
        );

    });

    $this->it( "validates that there is a target object before unsetting a value", function() {

        $this->expect( function() {

            $this->object_builder->build( function($array) {
                unset( $array[ 'name' ] );
            });

        }) ->to() ->raise(
            Haijin_Error::class,
            function($error) {
                $this->expect( $error->getMessage() ) ->to() ->equal(
                    "A target object must be set first with \$target->set_to( new Object() );"
                );
            }
        );

    });

    $this->it( "validates that there is a target object before asking if a value is set", function() {

        $this->expect( function() {

            $this->object_builder->build( function($array) {
                isset( $array[ 'name' ] );
            });

        }) ->to() ->raise(
            Haijin_Error::class,
            function($error) {
                $this->expect( $error->getMessage() ) ->to() ->equal(
                    "A target object must be set first with \$target->set_to( new Object() );"
                );
            }
        );

    });

    $this->it( "get the target object", function() {

        $this->object_builder->build( function($array) {
            $array->set_to( [] );

            $this->expect( $array->get_target() ) ->to() ->equal( [] );
        });

    });

});