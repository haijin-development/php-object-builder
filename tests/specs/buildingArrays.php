<?php

use Haijin\ObjectBuilder\ObjectBuilder;

$spec->describe("When building arrays", function () {

    $this->let("objectBuilder", function () {

        return new ObjectBuilder();

    });

    $this->it("builds an empty array", function () {

        $array = $this->objectBuilder->build(function ($array) {

            $array->setTo([]);

        });

        $this->expect($array)->to()->equal([]);

    });

    $this->it("adds items to the array", function () {

        $array = $this->objectBuilder->build(function ($array) {

            $array->setTo([]);

            $array[] = "Lisa";

            $array[] = "Simpson";

        });

        $this->expect($array)->to()->be()->exactlyLike([
            "Lisa",
            "Simpson"
        ]);

    });

    $this->it("builds nested arrays", function () {

        $array = $this->objectBuilder->build(function ($array) {

            $array->setTo([]);

            $array[] = "Lisa";

            $array[] = "Simpson";

            $array[] = $array->build(function ($array) {

                $array->setTo([]);

                $array[] = "Evergreen";

                $array[] = "742";

            });

        });

        $this->expect($array)->to()->be()->exactlyLike([
            "Lisa",
            "Simpson",
            [
                "Evergreen", "742"
            ]
        ]);

    });

    $this->it("builds arrays from a source object", function () {

        $user = [
            "name" => "Lisa",
            "lastName" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ];

        $array = $this->objectBuilder->build($user, function ($array, $user) {

            $array->setTo([]);

            $array[] = $user["name"];

            $array[] = $user["lastName"];

            $array[] = $array->build($user["address"],
                function ($array, $address) {

                    $array->setTo([]);

                    $parts = explode(" ", $address["street"]);

                    $array[] = $parts[0];

                    $array[] = $parts[1];

                });

        });

        $this->expect($array)->to()->be()->exactlyLike([
            "Lisa",
            "Simpson",
            [
                "Evergreen", "742"
            ]
        ]);

    });

    $this->describe('when using the ArrayAccess protocol', function () {

        $this->it('set a value', function () {

            $array = $this->objectBuilder->build(null, function ($array, $user) {

                $array->setTo([]);

                $array['user'] = 'Lisa';

            });

            $this->expect($array['user'])->to()->equal('Lisa');

        });

        $this->it('gets a value', function () {

            $array = $this->objectBuilder->build(null, function ($array, $user) {

                $array->setTo([]);

                $array['user'] = 'Lisa';

                $this->expect($array['user'])->to()->equal('Lisa');

            });

        });

        $this->it('unsets a value and test for presence', function () {

            $array = $this->objectBuilder->build(null, function ($array, $user) {

                $array->setTo([]);

                $array['user'] = 'Lisa';

                unset($array['user']);

                $this->expect(isset($array['user']))->to()->be()->false();

            });

        });

    });

});