<?php

namespace Haijin\Object_Builder;

use Haijin\Ordered_Collection;

class Object_Builder implements \ArrayAccess
{
    /**
     * The object being built.
     */
    public $target;

    /// Initializing

    /**
     * Initializes this Object_Builder.
     */
    public function __construct()
    {
        $this->target = null;
    }

    /// Accessing

    /**
     * Gets the target object being built.
     *
     * @return object The object being built.
     */
    public function get_target()
    {
        return $this->target;
    }

    /**
     * Sets the target object being built.
     */
    public function set_target($object)
    {
        $this->target = $object;
    }

    /**
     * Sets the target object being built.
     * It's more expressive in some contexts.
     */
    public function set_to($object)
    {
        $this->set_target( $object );
    }

    /**
     * Delegates the accessing of attributes and properties to the target object being built.
     *
     * @param string $name The name of the target attribute or public property.
     * @param object $value The value to set to the target attribute attribute or public property.
     */
    public function __set($name, $value)
    {
        $this->validate_target_object();

        $this->target->$name = $value;
    }

    /**
     * Delegates the call of a method to the target object being built.
     *
     * @param string $method_name The name of the method.
     * @param array $params The parameters of the method call.
     *
     * @return object Returns the result of calling the method to the target object.
     */
    public function __call($method_name, $params)
    {
        $this->validate_target_object();

        return $this->target->$method_name( ... $params );
    }

    /// Converting DSL

    /**
     * Creates a new Object_Builder and evaluates a callable DSL on it.
     *
     * Method signature
     *
     *      public function object(...$optional_values, $callable);
     *
     * @param object $optional_values Optional - 0 or more values to be 
     *  used as the building sources (or models).
     * @param callable $callable The callable with the building definition.
     *
     * @return object Returns the building target.
     */
    public function build( ...$params )
    {
        $new_builder = $this->new_builder_instance();

        return $new_builder->eval( ...$params );
    }

    /**
     * Evaluates a callable DSL in $this object.
     *
     * Method signature
     *
     *      public function object(...$optional_values, $callable);
     *
     * @param object $optional_values Optional - 0 or more values to be 
     *  used as the building sources (or models).
     * @param callable $callable The callable with the building definition.
     *
     * @return object Returns the building target.
     */
    public function eval(...$params)
    {
        $params_count = count( $params );

        $new_params = array_merge(
            [ $this ],
            array_slice( $params, 0, $params_count - 1 )
        );

        $callable = $params[ $params_count - 1 ];

        if( is_string( $callable ) ) {
            $callable = new $callable();
        }

        $callable( ...$new_params );

        return $this->target;
    }

    /// Validating

    protected function validate_target_object()
    {
        if( $this->target === null ) {
            throw new Haijin_Error( "A target object must be set first with \$target->set_to( new Object() );" );
        }
    }

    /// Creating instances

    protected function new_builder_instance()
    {
        $subclass = get_class( $this );

        return new $subclass();
    }

    /// ArrayAccess implementation
    
    public function offsetExists($offset)
    {
        return isset( $this->target[ $offset ] );
    }

    public function offsetGet($offset)
    {
        return $this->target[ $offset ];
    }

    public function offsetSet($offset, $value)
    {
        if( $offset === null ) {
            $offset = count( $this->target );
        }

        $this->target[ $offset ] = $value;
    }

    public function offsetUnset($offset)
    {
        unset( $this->target[ $offset ] );
    }
}