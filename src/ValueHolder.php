<?php

namespace Haijin\ObjectBuilder;

/**
 * This class is an artifact to improve the expressiveness of the ObjectBuilders.
 * It delegates calls from ->to_something() to this object into ->to_something( $value ) into the ObjectBuilder.
 *
 * Example:
 *
 *      $object = (new AppObjectBuilder() )->build( function($obj) use($user) {
 *          $obj->target = [];
 *
 *          $obj->address = $this->convert( $user->get_address() ) ->to_address();
 *      });
 */
class ValueHolder
{
    protected $object_builder;

    /**
     * Initializes the ValueHolder object.
     *
     * @param object $value The value to be passed back as a parameter to the ObjectBuilder.
     * @param ObjectBuilder $object_builder The ObjectBuilder that created $this ValueHolder.
     */
    public function __construct($value, $object_builder)
    {
        $this->value = $value;
        $this->object_builder = $object_builder;
    }

    /**
     * Delegates all methods back to the ObjectBuilder passing $this->value as the first parameter.
     *
     * @param string $method_name The name of the method to delegate to the ObjectBuilder.
     * @param array $arguments An array of arguments to pass to the delegated method in the ObjectBuilder.
     *
     * @return The converted value.
     */
    public function __call($method_name, $arguments)
    {
        $arguments = array_merge( [ $this->value], $arguments );

        return $this->object_builder->$method_name( ... $arguments );
    }
}