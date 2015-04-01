<?php

namespace Lce\web\util {
    class HandyObject
    {
        protected $_data = array();

        public function set($key, $val)
        {
            $this->_data[$key] = $val;
        }

        public function get($key, $defaultValue = null)
        {
            if (isset($this->_data[$key]))
                return $this->_data[$key];

            return $defaultValue;
        }

        public function has($key)
        {
            return isset($this->_data[$key]);
        }

        public function getData()
        {
            return $this->_data;
        }
    }

    class BaseUtil
    {
        public static function arrayGetValueByPath($array, $path, $defaultValue = null, $separator = '/')
        {
            if (is_array($array) && is_string($path)) {
                $path = trim($path, $separator);

                if (!strpos($path, $separator)) {
                    return isset($array[$path]) ? $array[$path] : $defaultValue;
                } else {
                    $path = explode($separator, $path);
                    $key = array_shift($path);

                    $array = isset($array[$key]) ? $array[$key] : $defaultValue;
                    $path = implode($separator, $path);

                    return self::arrayGetValueByPath($array, $path, $separator);
                }
            }

            return $defaultValue;
        }

        public static function arraySetValueByPath(&$array, $path, $value, $separator = '/')
        {
            if (is_array($array) && is_string($path)) {
                $path = trim($path, $separator);
                $path = explode($separator, $path);

                $key = array_shift($path);

                if (count($path) == 0) {
                    $array[$key] = $value;
                } else {
                    if (!array_key_exists($key, $array))
                        $array[$key] = array();

                    self::arraySetValueByPath($array[$key], implode($separator, $path), $value, $separator);
                }
            }
        }

        public static function arrayMerge($array1, $array2, $distinct = false)
        {
            if (!$distinct)
                return array_merge_recursive($array1, $array2);

            $merged = is_array($array1) ? $array1 : array();

            if (is_array($array2)) {
                foreach ($array2 as $key => &$value) {
                    if (is_array($value) && isset ($merged [$key]) && is_array($merged [$key])) {
                        $merged [$key] = self::arrayMerge($merged [$key], $value, true);
                    } else {
                        $merged [$key] = $value;
                    }
                }
            }

            return $merged;
        }

        public static function stringStartWith($haystack, $needle)
        {
            return $needle === '' || strpos($haystack, $needle) === 0;
        }

        public static function stringEndWith($haystack, $needle)
        {
            return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
        }

        public static function objectApplyPropertiesFromArray(&$object, array $array)
        {
            if (is_object($object)) {
                foreach ($array as $propertyName => $propertyValue) {
                    if (!isset($object->$propertyName)) {
                        $object->$propertyName = $array[$propertyName];
                    }
                }
            } else {
                foreach ($object as $propertyName => $propertyValue) {
                    if (!isset($object::$propertyName)) {
                        $object::$propertyName = $array[$propertyName];
                    }
                }
            }
        }

        public static function objectIsMethodExist($methodName, $object, $ignoreCase = true, $methodLevel = \ReflectionMethod::IS_PUBLIC)
        {
            if ($ignoreCase)
                $methodName = strtolower($methodName);

            $reflect = new \ReflectionClass($object);
            $methods = $reflect->getMethods($methodLevel);

            foreach ($methods as $method) {

                if (!$ignoreCase && $methodName === $method->getShortName())
                    return true;
                else if ($ignoreCase && $methodName === strtolower($method->getShortName()))
                    return true;
            }

            return false;
        }
    }
}