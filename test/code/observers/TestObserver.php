<?php

namespace Lce\test\code\observers {

    use Lce\web\event\EventContext;

    class TestObserver
    {
        public function test(EventContext $eventContext)
        {
            echo __METHOD__;
            $eventContext->stop();
        }

        public function test2()
        {
            echo __METHOD__;
        }
    }
}