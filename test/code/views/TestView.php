<?php

namespace Lce\test\code\views {
    use Lce\web\mvc\View;

    class TestView extends View
    {
        /**
         * @return string return __METHOD__ in your implement
         */
        function getDefaultCacheKey()
        {
            return __METHOD__;
        }
    }
}