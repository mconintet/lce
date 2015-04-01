<?php

namespace Lce\web\util\pinyin {
    use Lce\web\util\Curl;

    class PinyinUtil implements IPinyinUtil
    {
        const GATEWAY = 'http://toop123.duapp.com/pinyin/getpinyin.php';

        const FORMAT_CAPITAL = 1;
        const FORMAT_UPPERCASE = 2;
        const FORMAT_LOWERCASE = 4;
        const FORMAT_TONE = 8;

        const OPT_FORMAT_KEY = 'format';

        private static $_instance = null;

        private function __construct()
        {
        }

        public static function getInstance()
        {
            if (self::$_instance === null)
                self::$_instance = new self;

            return self::$_instance;
        }

        /**
         * @param $chinese
         * @param array $options
         * @return mixed|string
         */
        public function fromChinese($chinese, $options = array())
        {
            $curl = new Curl(self::GATEWAY);
            $curl->usePost(true);

            $params = array(
                'word' => $chinese
            );

            if (isset($options[self::OPT_FORMAT_KEY])) {
                $format = $options[self::OPT_FORMAT_KEY];
                if (self::FORMAT_CAPITAL & $format) {
                    $params['rad'] = 'large';
                } else if (self::FORMAT_LOWERCASE & $format) {
                    $params['rad'] = 'lower';
                } else if (self::FORMAT_UPPERCASE & $format) {
                    $params['rad'] = 'upper';
                }

                if (self::FORMAT_TONE & $format) {
                    $params['ct'] = 'tone';
                }
            }

            $result = $curl->exec($params);

            if ($curl->isSuccess()) {
                $result = json_decode($result->body, true);
                if (is_array($result) && isset($result['ok']) && $result['ok'] === '200')
                    return $result['word'];
            }

            return '';
        }
    }
}