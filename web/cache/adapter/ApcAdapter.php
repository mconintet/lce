<?php

namespace Lce\web\cache\adapter {

    use Lce\web\cache\ICacheAdapter;

    class ApcAdapter implements ICacheAdapter
    {
        public function get($key)
        {
            // TODO: Implement get() method.
        }

        public function set($key, $value, $expire)
        {
            // TODO: Implement set() method.
        }

        public function remove($key)
        {
            // TODO: Implement remove() method.
        }

        public function clearAll()
        {
            // TODO: Implement clearAll() method.
        }

        public function has($key)
        {
            // TODO: Implement has() method.
        }
    }
}