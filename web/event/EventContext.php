<?php
namespace Lce\web\event {
    use Lce\web\util\HandyObject;

    class EventContext extends HandyObject
    {
        private $_stop = false;

        public function stop()
        {
            $this->_stop = true;
        }

        public function isStopped()
        {
            return $this->_stop === true;
        }

        public function setParams($params)
        {
            if (is_array($params)) {
                foreach ($params as $key => $val) {
                    $this->set($key, $val);
                }
            }
        }
    }
}