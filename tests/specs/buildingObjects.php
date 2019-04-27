<?php

namespace Building_Objects_Spec;

use Haijin\ObjectBuilder\ObjectBuilder;

$spec->describe("When building objects", function () {

    $this->let("objectBuilder", function () {

        return new ObjectBuilder();

    });

    $this->it("builds an empty object", function () {

        $user = $this->objectBuilder->build(function ($obj) {

            $obj->setTo(new User());

        });

        $this->expect($user)->to()->be()->a('Building_Objects_Spec\User');

    });

    $this->it("adds attributes to the built object", function () {

        $user = $this->objectBuilder->build(function ($user) {

            $user->setTo(new User());

            $user->setName("Lisa");

            $user->setLastName("Simpson");

        });

        $this->expect($user)->to()->be()->exactlyLike([
            "getName()" => "Lisa",
            "getLastName()" => "Simpson",
        ]);

    });

    $this->it("adds nested attributes to the built object", function () {

        $user = $this->objectBuilder->build(function ($user) {

            $user->setTo(new User());

            $user->setName("Lisa");

            $user->setLastName("Simpson");

            $user->setAddress(

                $user->build(function ($address) {

                    $address->setTo(new Address());

                    $address->setStreet("Evergreen");

                    $address->setNumber(742);

                })

            );

        });

        $this->expect($user)->to()->be()->exactlyLike([
            "getName()" => "Lisa",
            "getLastName()" => "Simpson",
            "getAddress()" => [
                "getStreet()" => "Evergreen",
                "getNumber()" => 742
            ]
        ]);

    });

});

class Address
{
    protected $street;
    protected $number;

    public function setStreet($street)
    {
        $this->street = $street;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setNumber($number)
    {
        $this->number = $number;
    }

    public function getNumber()
    {
        return $this->number;
    }
}

class User
{
    protected $name;
    protected $lastName;
    protected $address;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }
}
