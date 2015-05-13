<?php

namespace Assimtech\Tempo\ArrayObject;

use ArrayObject;

abstract class ValidatableArrayObject extends ArrayObject
{
    /**
     * {@inheritdoc}
     */
    public function __construct($input = array(), $flags = 0, $iteratorClass = 'ArrayIterator')
    {
        parent::__construct($input, $flags, $iteratorClass);

        $this->validate();
    }

    /**
     * @param string|null $index The index to validate, if null, validate all properties
     * @return void
     * @throws \InvalidArgumentException
     */
    abstract protected function validate($index = null);

    /**
     * {@inheritdoc}
     */
    public function offsetSet($index, $newval)
    {
        parent::offsetSet($index, $newval);

        $this->validate($index);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($index)
    {
        parent::offsetUnset($index);

        $this->validate($index);
    }
}
