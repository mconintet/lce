<?php

namespace Lce\web\mvc\model {
    class StringValidation
    {
        public static function isNumeric($value)
        {
            return is_numeric($value);
        }

        public static function notEmpty($value, $whitespace = false)
        {
            if ($whitespace)
                return $value !== '';

            return trim($value, ' ') !== '';
        }

        public static function isEmpty($value, $whitespace = false)
        {
            return !self::notEmpty($value, $whitespace);
        }

        public static function isDate($value)
        {
            return strtotime($value) !== false;
        }

        public static function regExp($pattern, $value)
        {
            return preg_match($pattern, $value, $matches) === 1;
        }

        public static function isChinese($value)
        {
            return self::regExp('/^[\u4e00-\u9fa5]+$/', $value);
        }

        public static function hasChinese($value)
        {
            return self::regExp('/[\u4e00-\u9fa5]+/', $value);
        }

        public static function isEmail($value)
        {
            return self::regExp('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $value);
        }

        public static function isUrl($value)
        {
            return self::regExp('/^[a-zA-z]+://[^\s]+$/', $value);
        }

        public static function isChineseTel($value)
        {
            return self::regExp('/^(?:\d{3}-\d{8}|\d{4}-\d{7})$/', $value);
        }

        public static function isQq($value)
        {
            return self::regExp('/^[1-9][0-9]{4,}$/', $value);
        }

        public static function isChineseIdCard($value)
        {
            return self::regExp('/^(?:\d{15}|\d{18})$/', $value);
        }

        public static function isHtmlTags($value)
        {
            return self::regExp('/<(\/)?\w+/', $value);
        }

        public static function isGreatThanZero($value)
        {
            return intval($value) > 0;
        }

        public static function lengthRange($value, $min, $max)
        {
            $len = strlen($value);
            return $len >= $min && $len <= $max;
        }

        public static function numericRange($value, $min, $max)
        {
            if (is_numeric($value)) {
                $value = floatval($value);
                return $value >= $min && $value <= $max;
            }

            return false;
        }

        public static function isInt($value)
        {
            if ($value === '0')
                return true;

            return self::regExp('/^-\d+$/', $value) && intval($value) !== 0;
        }

        public static function IntRange($value, $min, $max)
        {
            if (self::isInt($value)) {
                $value = intval($value);
                return $value >= $min && $value <= $max;
            }

            return false;
        }

        public static function isFloat($value)
        {
            if (self::regExp('/^0\.0+$/', $value))
                return true;

            return self::regExp('/^-?\d*\.\d+$/', $value) && floatval($value) !== INF;
        }

        public static function floatRange($value, $min, $max)
        {
            if (self::isFloat($value)) {
                $value = floatval($value);
                return $value >= $min && $value <= $max;
            }

            return false;
        }
    }
}