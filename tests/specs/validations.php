<?php

use Haijin\Errors\HaijinError;
use Haijin\ObjectBuilder\ObjectBuilder;

$spec->describe("When validating the ObjectBuilder", function () {

    $this->let("objectBuilder", function () {

        return new ObjectBuilder();

    });

    $this->it("validates that there is a target object before setting a value", function () {

        $this->expect(function () {

            $this->objectBuilder->build(function ($array) {
                $array['name'] = "Lisa";
            });

        })->to()->raise(
            HaijinError::class,
            function ($error) {
                $this->expect($error->getMessage())->to()->equal(
                    "A target object must be set first with \$target->setTo( new Object() );"
                );
            }
        );

    });

    $this->it("validates that there is a target object before getting a value", function () {

        $this->expect(function () {

            $this->objectBuilder->build(function ($array) {
                $array['name'];
            });

        })->to()->raise(
            HaijinError::class,
            function ($error) {
                $this->expect($error->getMessage())->to()->equal(
                    "A target object must be set first with \$target->setTo( new Object() );"
                );
            }
        );

    });

    $this->it("validates that there is a target object before unsetting a value", function () {

        $this->expect(function () {

            $this->objectBuilder->build(function ($array) {
                unset($array['name']);
            });

        })->to()->raise(
            HaijinError::class,
            function ($error) {
                $this->expect($error->getMessage())->to()->equal(
                    "A target object must be set first with \$target->setTo( new Object() );"
                );
            }
        );

    });

    $this->it("validates that there is a target object before asking if a value is set", function () {

        $this->expect(function () {

            $this->objectBuilder->build(function ($array) {
                isset($array['name']);
            });

        })->to()->raise(
            HaijinError::class,
            function ($error) {
                $this->expect($error->getMessage())->to()->equal(
                    "A target object must be set first with \$target->setTo( new Object() );"
                );
            }
        );

    });

    $this->it("get the target object", function () {

        $this->objectBuilder->build(function ($array) {
            $array->setTo([]);

            $this->expect($array->getTarget())->to()->equal([]);
        });

    });

});