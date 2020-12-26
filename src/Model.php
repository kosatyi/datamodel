<?php

namespace Kosatyi\DataModel;

use JsonSerializable;
use Serializable;
use Iterator;

/**
 * Class Model
 * @package Kosatyi\DataModel
 */
class Model implements Iterator, JsonSerializable, Serializable
{
    /**
     *
     */
    const SEPARATOR = '.';
    /**
     * @var Model
     */
    private $__parent__;
    /**
     * @var null
     */
    private $__path__;
    /**
     * @var array
     */
    private $__data__;
    /**
     * @var int
     */
    private $position = 0;

    /**
     * Model constructor.
     * @param null $data
     * @param null $parent
     * @param null $path
     */
    public function __construct($data = [], $parent = null, $path = null)
    {
        $this->__data__ = $data;
        $this->__parent__ = $parent;
        $this->__path__ = $path;
        $this->rewind();
    }

    /**
     *
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->nested($this->__data__[$this->position], $this->position);
    }

    /**
     * @return bool|float|int|string|null
     */
    public function key()
    {
        return $this->position;
    }

    /**
     *
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->__data__[$this->position]);
    }

    /**
     * @param $method
     * @param array $params
     * @return false|mixed|null
     */
    public function __call($method, $params = [])
    {
        $result = null;
        if (method_exists($this, $method)) {
            $result = call_user_func_array([$this, $method], $params);
        }
        return $result;
    }

    public function __invoke()
    {

    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    public function __set($name, $value)
    {
        return $this->attr($name, $value);
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->attr($name)->all();
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->attr($name);
    }

    /**
     * @param array $object
     * @param array $keys
     * @return mixed
     */
    public function nested($object = [], $keys = [])
    {
        return new $this($object, $this, $keys);
    }

    /**
     * @param null $keys
     * @return mixed
     */
    public function copy($keys = null)
    {
        return new $this($this->attr($keys)->all());
    }

    /**
     * @param $path
     * @return array|mixed
     */
    protected function path($path)
    {
        $parts = array_filter(explode(static::SEPARATOR, (string)$path), 'strlen');
        $parts = array_reduce($parts, static function ($a, $v) {
            $a[] = ctype_digit($v) ? (int)$v : $v;
            return $a;
        }, []);
        return $parts;
    }

    /**
     * @param $keys
     * @param null $value
     * @return $this|array|mixed|null
     */
    public function attr($keys = null, $value = null)
    {
        if ($keys === null) {
            return $this;
        }
        if (is_string($keys)) {
            $keys = $this->path($keys);
        }
        $path = $keys;
        if ($value === null) {
            $copy = $this->__data__;
        } else {
            $copy =& $this->__data__;
        }
        while (count($keys)) {
            if ($copy instanceof $this) {
                return $copy->attr($keys, $value);
            }
            if (is_callable($copy)) {
                return $copy($keys, $value);
            }
            $key = array_shift($keys);
            if (is_object($copy)) {
                $copy =& $copy->{$key};
            } else {
                $copy =& $copy[$key];
            }
        }
        if ($value === null) {
            return $this->nested($copy, $path);
        }
        if (is_callable($copy)) {
            $copy($value);
        } else {
            $copy = $value;
        }
        return $this->update();
    }

    /**
     * @param array $data
     * @return $this
     */
    public function data(array $data = [])
    {
        $this->__data__ = $data;
        return $this->update();
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->__data__;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this->all());
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        $this->data(unserialize($data));
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->all();
    }

    public function __toString()
    {
        return (string) $this->all();
    }

    /**
     *
     */
    public function update()
    {
        if ($this->__parent__ && $this->__path__) {
            $this->__parent__->attr($this->__path__, $this->__data__);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function append()
    {
        return $this->data(array_merge($this->__data__, func_get_args()));
    }

    /**
     * @return $this
     */
    public function prepend()
    {
        return $this->data(array_merge(func_get_args(), $this->__data__));
    }

    /**
     * @param $keys
     * @param $value
     * @return $this|array|mixed|null
     */
    public function set($keys,$value)
    {
        return $this->attr($keys,$value);
    }

    /**
     * @param $keys
     * @return $this|array|mixed|null
     */
    public function get($keys){
        return $this->attr($keys);
    }

    /**
     * @param $keys
     * @return $this
     */
    public function increment($keys)
    {
        $value = $this->attr($keys)->all();
        $value++;
        $this->attr($keys, $value);
        return $this->update();
    }

    /**
     * @param $keys
     * @return $this
     */
    public function decrement($keys)
    {
        $value = $this->attr($keys)->all();
        $value--;
        $this->attr($keys, $value);
        return $this->update();
    }

    /**
     * @param $callback
     * @return mixed
     */
    public function filter($callback)
    {
        $data = [];
        foreach ($this->all() as $index => $value) {
            if ($result = $callback($value, $index, $this->__data__)) {
                array_push($data, $value);
            }
        }
        return $this->data($data);
    }

    /**
     * @param $callback
     * @return $this
     */
    public function map($callback)
    {
        $data = [];
        foreach ($this->__data__ as $index => $value) {
            if ($result = $callback($value, $index, $this->__data__)) {
                array_push($data, $result);
            }
        }
        return $this->data($data);
    }
}