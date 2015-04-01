<?php

return array(
    'application' => array(
        'directory' => '/Users/hsiaosiyuan/www',
        'namespace' => 'Lce\test',
        'timezone' => 'Asia/Shanghai',
        'errorTemplate' => '/Users/hsiaosiyuan/www/Lce/test/code/data/error.html'
    ),
    'logger' => array(
        'enable' => false,
        'adapter' => '\Lce\web\log\adapter\FileAdapter',
        'logFileLocation' => '/Users/hsiaosiyuan/www/Lce/test/log',
        'dbFileLocation' => '/Users/hsiaosiyuan/www/Lce/test/log.db'
    ),
    'router' => array(
        'rewrite' => true,
        'ignoreCase' => true,
        'notFoundTemplate' => '/Users/hsiaosiyuan/www/Lce/test/code/data/notFound.html',
        'regexpRouters' => array(
            array(
                'from' => array('user', '(\d+)'),
                'paramMap' => array('id'),
                'to' => 'index/index'
            )
        )
    ),
    'event' => array(
        'observers' => array(
            'test' => array(
                array(
                    'class' => '\Lce\test\code\observers\TestObserver',
                    'method' => 'test'
                ),
                array(
                    'class' => '\Lce\test\code\observers\TestObserver',
                    'method' => 'test2'
                )
            )
        )
    )
);