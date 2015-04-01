<?php

namespace Lce\web\cache {
    interface ICacheAdapter
    {
        public function get($key);

        public function set($key, $value, $expire);

        public function remove($key);

        public function clearAll();

        public function has($key);
    }

    class CacheManager
    {

    }
}