<?php

namespace JsonBuilder_Integration_Spec;

$spec->describe("When building arrays", function () {

    $this->it("Integration", function () {

        $action = new Action();

        $json = $action->getJson();

        $this->expect($json)->to()->be()->exactlyLike([
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
                                "number" => function ($value) {
                                    $this->expect($value)->to()->be("===")->than(742);
                                }
                            ]
                        ]
                    ]
                ]
            ]
        ]);

    });

});

class Address
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

class User
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
        return [new Address()];
    }
}

use Haijin\ObjectBuilder\JsonBuilder;

class Action
{
    public function getJson()
    {
        $user = new User();

        return $this->buildJsonFrom($user);
    }

    protected function buildJsonFrom($user)
    {
        $jsonBuilder = new JsonBuilder();

        return $jsonBuilder->build($user, function ($json, $user) {

            $json->response = $this->successResponseToJson($json, $user);

        });
    }

    protected function successResponseToJson($json, $user)
    {
        return $json->build($user, function ($json, $user) {

            $json->apiVersion = "1.0.0";

            $json->success = true;

            $json->data = [
                "user" => $this->userToJson($json, $user)
            ];
        });
    }

    protected function userToJson($json, $user)
    {
        return $json->build($user, function ($json, $user) {

            $json->name = $user->getName();

            $json->lastName = $user->getLastName();

            $json->addresses = array_map(
                function ($eachAddress) use ($json) {
                    return $this->addressToJson($json, $eachAddress);
                },
                $user->getAddresses()
            );
        });
    }

    protected function addressToJson($json, $address)
    {
        return $json->build($address, function ($json, $address) {

            $json->street = $json->toString($address->getStreet());

            $json->number = $json->toInt($address->getNumber());
        });
    }
}