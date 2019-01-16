<?php

namespace Haijin\Object_Builder;

/**
 * This class is an artifact to improve the expressiveness of the ObjectBuilders.
 * It delegates calls from ->to_something() to this object into ->to_something( $value ) into
 * the Object_Builder.
 *
 * Example:
 *
 *      $object = (new AppObjectBuilder() )->build( function($obj) use($user) {
 *          $obj->target = [];
 *
 *          $obj->address = $this->convert( $user->get_address() ) ->to_address();
 *      });
 */
class Value_Holder
{
    protected $object_builder;

    /**
     * Initializes the Value_Holder object.
     *
     * @param object $value The value to be passed back as a parameter to the Object_Builder.
     * @param Object_Builder $object_builder The Object_Builder that created $this Value_Holder.
     */
    public function __construct($value, $object_builder)
    {
        $this->value = $value;
        $this->object_builder = $object_builder;
    }

    /**
     * Delegates all methods back to the Object_Builder passing $this->value as the first parameter.
     *
     * @param string $method_name The name of the method to delegate to the Object_Builder.
     * @param array $arguments An array of arguments to pass to the delegated method in the Object_Builder.
     *
     * @return The converted value.
     */
    public function __call($method_name, $arguments)
    {
        $arguments = array_merge( [ $this->value], $arguments );

        return $this->object_builder->$method_name( ... $arguments );
    }
}