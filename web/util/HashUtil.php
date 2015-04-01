<?php

namespace Lce\web\util{
    class HashGenerated
    {
        public $hashType;
        public $hash;
        public $salt;
    }

    /**
     * Class Password this is a simple class wrap for crypt
     * @package Lce\web\util
     */
    class HashUtil
    {
        //details about these const please see http://www.php.net/manual/zh/function.crypt.php
        const CRYPT_STD_DES = 'CRYPT_STD_DES';
        const CRYPT_EXT_DES = 'CRYPT_EXT_DES';
        const CRYPT_MD5 = 'CRYPT_MD5';
        const CRYPT_SHA256 = 'CRYPT_SHA256';
        const CRYPT_SHA512 = 'CRYPT_SHA512';

        private static $_INSTANCE = null;

        private function __construct()
        {
        }

        public static function getInstance()
        {
            if (self::$_INSTANCE === null)
                self::$_INSTANCE = new self;

            return self::$_INSTANCE;
        }

        public static $HASH_TYPES = array(
            self::CRYPT_STD_DES,
            self::CRYPT_EXT_DES,
            self::CRYPT_MD5,
            self::CRYPT_SHA256,
            self::CRYPT_SHA512
        );

        /**
         * @param $str
         * @param $hashType
         * @param null $salt
         * @param int $round
         * @return HashGenerated
         * @throws \Exception
         */
        public function generate($str, $hashType, $salt = null, $round = 5000)
        {
            if (empty($str))
                throw new \Exception('string need to be hashed can not be empty');

            if (!in_array($hashType, self::$HASH_TYPES))
                throw new \Exception('hash type is invalid');

            if (constant($hashType) === 0)
                throw new \Exception($hashType . ' haven\'t been supported on this system');

            return $this->$hashType($str, $salt, $round);
        }

        public function verify($str, $hash, $hashType, $salt = null)
        {
            if (empty($str))
                throw new \Exception('string need to be hashed can not be empty');

            if (!in_array($hashType, self::$HASH_TYPES))
                throw new \Exception('hash type is invalid');

            if (constant($hashType) === 0)
                throw new \Exception($hashType . ' haven\'t been supported on this system');

            $generated = $this->$hashType($str, $salt);

            return $generated->hash === $hash;
        }

        private function _escapeSalt($salt)
        {
            $base64_digits =
                'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
            $bcrypt64_digits =
                './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

            $base64_string = base64_encode($salt);

            return strtr(rtrim($base64_string, '='), $base64_digits, $bcrypt64_digits);
        }

        private function _makeSalt($length)
        {
            $length = intval($length);

            $salt = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            $salt = $this->_escapeSalt($salt);
            $salt = substr($salt, 0, $length);

            if ($salt === false)
                throw new \Exception('failed to make default salt');

            return $salt;
        }

        /**
         * note: if use this method you must keep your string length less then 8
         * @param $str
         * @param null $salt
         * @return HashGenerated
         * @throws \Exception
         */
        public function CRYPT_STD_DES($str, $salt = null)
        {
            if ($salt !== null && preg_match('/^[.\/a-zA-Z0-9]{2}$/', $salt, $matches) !== 1) {
                throw new \Exception("salt error, only can contains ./0-9A-Za-z");
            }

            if ($salt === null) {
                $salt = $this->_makeSalt(2);
            }

            $generated = new HashGenerated();
            $generated->hashType = 'CRYPT_STD_DES';
            $generated->hash = crypt($str, $salt);
            $generated->salt = $salt;

            return $generated;
        }

        /**
         * from my tests on mac os, this method was very slow
         * @param $str
         * @param null $salt
         * @return HashGenerated
         * @throws \Exception
         */
        public function CRYPT_EXT_DES($str, $salt = null)
        {
            if ($salt !== null && preg_match('/^_[.\/a-zA-Z0-9]{8}$/', $salt, $matches) !== 1) {
                throw new \Exception("salt error, only can contains ./0-9A-Za-z");
            }

            if ($salt === null) {
                $salt = '_' . $this->_makeSalt(8);
            }

            $generated = new HashGenerated();
            $generated->hashType = 'CRYPT_EXT_DES';
            $generated->hash = crypt($str, $salt);
            $generated->salt = $salt;

            return $generated;
        }

        public function CRYPT_MD5($str, $salt = null)
        {
            if ($salt !== null && strlen(preg_replace('/^\$1\$/', '', $salt)) !== 9) {
                throw new \Exception('your salt length muse be 1');
            }

            if ($salt === null) {
                $salt = $this->_makeSalt(8);
                $salt = '$1$' . $salt . '$';
            }

            $generated = new HashGenerated();
            $generated->hashType = 'CRYPT_EXT_DES';
            $generated->hash = crypt($str, $salt);
            $generated->salt = $salt;

            return $generated;
        }

        public function CRYPT_SHA256($str, $salt = null, $round = 5000)
        {
            if ($round < 1000 || $round > 999999999) {
                throw new \Exception('round must between 1000 ~ 999,999,999');
            }

            if ($salt === null) {
                $salt = $this->_makeSalt(16);
                $salt = '$5$rounds=' . $round . '$' . $salt . '$';
            } else {
                if (preg_match('/^\$5\$rounds=[1-9]\d{3,8}\$.*\$$/i', $salt, $matches) !== 1) {
                    throw new \Exception('salt length must less or equal then 16');
                }
            }

            $generated = new HashGenerated();
            $generated->hashType = 'CRYPT_EXT_DES';
            $generated->hash = crypt($str, $salt);
            $generated->salt = $salt;

            return $generated;
        }

        public function CRYPT_SHA512($str, $salt = null, $round = 5000)
        {
            if ($round < 1000 || $round > 999999999) {
                throw new \Exception('round must between 1000 ~ 999,999,999');
            }

            if ($salt === null) {
                $salt = $this->_makeSalt(64);
                $salt = '$6$rounds=' . $round . '$' . $salt . '$';
            } else {
                if (preg_match('/^\$6\$rounds=[1-9]\d{3,8}\$.*\$$/i', $salt, $matches) !== 1) {
                    throw new \Exception('salt length must less or equal then 16');
                }
            }

            $generated = new HashGenerated();
            $generated->hashType = 'CRYPT_EXT_DES';
            $generated->hash = crypt($str, $salt);
            $generated->salt = $salt;

            return $generated;
        }
    }
}