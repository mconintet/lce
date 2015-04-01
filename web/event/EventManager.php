<?php

namespace Lce\web\event {
    use Lce\web\config\Configurable;

    class EventManager extends Configurable
    {
        const CONFIG_KEY_LISTENER_CLASS = 'class';
        const CONFIG_KEY_LISTENER_METHOD = 'method';

        public $observers;
        private $_hasObservers;
        private $_listenerClassCache = array();

        private static $_instance = null;

        protected function __construct()
        {
            parent::__construct();
            $this->_hasObservers = is_array($this->observers) && count($this->observers);
        }

        public static function getInstance()
        {
            if (self::$_instance === null)
                self::$_instance = new self;

            return self::$_instance;
        }

        public function getListener($listenerClassName)
        {
            if (!isset($this->_listenerClassCache[$listenerClassName]))
                $this->_listenerClassCache[$listenerClassName] = new $listenerClassName;

            return $this->_listenerClassCache[$listenerClassName];
        }

        public function dispatch($eventName, EventContext $eventContext = null)
        {
            if ($this->_hasObservers) {
                if (isset($this->observers[$eventName]) &&
                    is_array($listeners = $this->observers[$eventName])
                ) {
                    $eventContext = $eventContext === null ? new EventContext() : $eventContext;
                    $isStopped = false;

                    foreach ($listeners as $listener) {
                        if ($isStopped) {
                            break;
                        }

                        if (is_array($listener)) {
                            if (isset($listener[self::CONFIG_KEY_LISTENER_CLASS]) &&
                                isset($listener[self::CONFIG_KEY_LISTENER_METHOD])
                            ) {
                                $observer = $this->getListener($listener[self::CONFIG_KEY_LISTENER_CLASS]);
                                $method = $listener[self::CONFIG_KEY_LISTENER_METHOD];
                                $observer->$method($eventContext);

                                $isStopped = $eventContext->isStopped();
                            }
                        }
                    }
                }
            }
        }

        /**
         * @return string
         */
        public static function getConfigSectionKey()
        {
            return 'event';
        }
    }
}