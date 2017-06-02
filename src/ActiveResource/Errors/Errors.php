<?php

namespace ActiveResource\Errors;
use ActiveResource\Ext\Inflector;

/**
 * Entity validation errors holder
 */
class Errors
{
    protected $class;
    protected $messages = array();

    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * Clear all messages
     */
    public function clear()
    {
        $this->messages = array();
    }

    /**
     * Add message to the error messages on attribute
     *
     * @param string $key
     * @param string $value
     */
    public function add($key, $value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }
        foreach ($value as $item) {
            $this->messages[$key][] = (string)$item;
        }
    }

    /**
     * Set messages for key to value
     *
     * @param string $key
     * @param mixed $value string or array of strings
     */
    public function set($key, $value)
    {
        $this->messages[$key] = is_array($value) ? $value : array($value);
    }

    /**
     * Get messages for key
     *
     * @param string $key
     * @return array
     */
    public function get($key)
    {
        if (isset($this->messages[$key]))
        {
            return $this->messages[$key];
        }
    }

    /**
     * Return count of all messages
     *
     * @return integer
     */
    public function getCount()
    {
        $count = 0;
        foreach ($this->messages as $k => $a)
        {
            $count += count($a);
        }
        return $count;
    }

    /**
     * Get attribute keys
     *
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->messages);
    }

    /**
     * Returns whole message array
     *
     * @return arary
     */
    public function toArray()
    {
        return $this->messages;
    }

    /**
     * Return true if no message errors
     *
     * @return boolean
     */
    public function isEmpty()
    {
        foreach($this->messages as $k => $a)
        {
            if (!empty($a))
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns all the full message errors as an array
     *
     * @return array
     */
    public function getFullMessages()
    {
        $a = array();
        foreach($this->messages as $attribute => $messages)
        {
            if ($attribute == 'base')
            {
                $a = array_merge($a, $messages);
            }
            else
            {
                foreach($messages as $message)
                {
                    $a[] = sprintf("%s %s", Inflector::humanize($attribute), $message);
                }
            }
        }
        return $a;
    }

}
