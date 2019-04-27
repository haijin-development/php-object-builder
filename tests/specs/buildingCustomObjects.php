<?php

namespace Custom_ObjectBuilders_Spec;

use Haijin\ObjectBuilder\ObjectBuilder;

$spec->describe("When building custom objects", function () {

    $this->let("objectBuilder", function () {

        return new ObjectBuilder();

    });

    $this->it("builds the object with an ObjectBuilder instance", function () {

        $source = ["Lisa", "Simpson", ["Evergreen", "742"]];

        $object = $this->objectBuilder->build($source, function ($obj, $source) {

            $obj->setTo(new \stdclass());

            $obj->name = "Lisa";
            $obj->lastName = "Simpson";

            $obj->address = $obj->build($source[2], new AddressBuilderCallable());
        });

        $this->expect($object)->to()->be()->exactlyLike([
            "name" => "Lisa",
            "lastName" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);

    });

    $this->it("builds the object with an ObjectBuilder class", function () {

        $source = ["Lisa", "Simpson", ["Evergreen", "742"]];

        $object = $this->objectBuilder->build($source, function ($obj, $source) {

            $obj->setTo(new \stdclass());

            $obj->name = "Lisa";

            $obj->lastName = "Simpson";

            $obj->address = $obj->build($source[2], AddressBuilderCallable::class);

        });

        $this->expect($object)->to()->be()->exactlyLike([
            "name" => "Lisa",
            "lastName" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);

    });

    $this->it("builds the object with an ObjectBuilder converter method class", function () {

        $source = ["Lisa", "Simpson", ["Evergreen", "742"]];

        $customObjectBuilder = new CustomObjectBuilder();

        $object = $customObjectBuilder->build($source, function ($obj, $source) {

            $obj->setTo(new \stdclass());

            $obj->name = "Lisa";

            $obj->lastName = "Simpson";

            $obj->address = $obj->toAddress($source[2]);

        });

        $this->expect($object)->to()->be()->exactlyLike([
            "name" => "Lisa",
            "lastName" => "Simpson",
            "address" => [
                "street" => "Evergreen 742"
            ]
        ]);

    });

});

class AddressBuilderCallable
{
    public function __invoke($address, $source)
    {
        $address->setTo(new \stdclass());

        $address->street = $source[0] . " " . $source[1];
    }
}

class AddressBuilder
{
    public function __invoke($address, $source)
    {
        $address->setTo(new \stdclass());

        $address->street = $source[0] . " " . $source[1];
    }
}

class CustomObjectBuilder extends ObjectBuilder
{
    public function toAddress($source)
    {
        return $this->build($source, function ($address, $source) {

            $address->setTo(new \stdclass());

            $address->street = $source[0] . " " . $source[1];

        });
    }
}