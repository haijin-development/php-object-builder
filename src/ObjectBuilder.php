<?php

namespace Haijin\ObjectBuilder;

use Haijin\Errors\HaijinError;

class ObjectBuilder implements \ArrayAccess
{
    /**
     * The object being built.
     */
    public $target;

    /// Initializing

    /**
     * Initializes this ObjectBuilder.
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
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Sets the target object being built.
     */
    public function setTarget($object)
    {
        $this->target = $object;
    }

    /**
     * Sets the target object being built.
     * It's more expressive in some contexts.
     */
    public function setTo($object)
    {
        $this->setTarget($object);
    }

    /**
     * Delegates the accessing of attributes and properties to the target object being built.
     *
     * @param string $name The name of the target attribute or public property.
     * @param object $value The value to set to the target attribute attribute or public property.
     */
    public function __set($name, $value)
    {
        $this->validateTargetObject();

        $this->target->$name = $value;
    }

    /**
     * Delegates the call of a method to the target object being built.
     *
     * @param string $methodName The name of the method.
     * @param array $params The parameters of the method call.
     *
     * @return object Returns the result of calling the method to the target object.
     */
    public function __call($methodName, $params)
    {
        $this->validateTargetObject();

        return $this->target->$methodName(... $params);
    }

    /// Converting DSL

    /**
     * Creates a new ObjectBuilder and evaluates a callable DSL on it.
     *
     * Method signature
     *
     *      public function object(...$optionalValues, $callable);
     *
     * @param object $optionalValues Optional - 0 or more values to be
     *  used as the building sources (or models).
     * @param callable $callable The callable with the building definition.
     *
     * @return object Returns the building target.
     */
    public function build(...$params)
    {
        $newBuilder = $this->newBuilderInstance();

        return $newBuilder->eval(...$params);
    }

    /**
     * Evaluates a callable DSL in $this object.
     *
     * Method signature
     *
     *      public function object(...$optionalValues, $callable);
     *
     * @param object $optionalValues Optional - 0 or more values to be
     *  used as the building sources (or models).
     * @param callable $callable The callable with the building definition.
     *
     * @return object Returns the building target.
     */
    public function eval(...$params)
    {
        $paramsCount = count($params);

        $newParams = array_merge(
            [$this],
            array_slice($params, 0, $paramsCount - 1)
        );

        $callable = $params[$paramsCount - 1];

        if (is_string($callable)) {
            $callable = new $callable();
        }

        $callable(...$newParams);

        return $this->target;
    }

    /// Validating

    protected function validateTargetObject()
    {
        if ($this->target === null) {
            throw new HaijinError("A target object must be set first with \$target->setTo( new Object() );");
        }
    }

    /// Creating instances

    protected function newBuilderInstance()
    {
        $subclass = get_class($this);

        return new $subclass();
    }

    /// ArrayAccess implementation

    public function offsetExists($offset)
    {
        $this->validateTargetObject();

        return isset($this->target[$offset]);
    }

    public function offsetGet($offset)
    {
        $this->validateTargetObject();

        return $this->target[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->validateTargetObject();

        if ($offset === null) {
            $offset = count($this->target);
        }

        $this->target[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        $this->validateTargetObject();

        unset($this->target[$offset]);
    }
}