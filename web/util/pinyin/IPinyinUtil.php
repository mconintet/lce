<?php

namespace Lce\web\util\pinyin {
    interface IPinyinUtil
    {
        /**
         * @param $chinese
         * @param array $options
         * @return mixed
         */
        public function fromChinese($chinese, $options = array());
    }
}