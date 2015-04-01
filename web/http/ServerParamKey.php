<?php

namespace Lce\web\http {
    class ServerParamKey
    {
        const ARGV = 'argv';
        const ARGC = 'argc';
        const GATEWAY_INTERFACE = 'GATEWAY_INTERFACE';
        const SERVER_ADDR = 'SERVER_ADDR';
        const SERVER_NAME = 'SERVER_NAME';
        const SERVER_SOFTWARE = 'SERVER_SOFTWARE';
        const SERVER_PROTOCOL = 'SERVER_PROTOCOL';
        const REQUEST_METHOD = 'REQUEST_METHOD';
        const REQUEST_TIME = 'REQUEST_TIME';
        const REQUEST_TIME_FLOAT = 'REQUEST_TIME_FLOAT';
        const QUERY_STRING = 'QUERY_STRING';
        const DOCUMENT_ROOT = 'DOCUMENT_ROOT';
        const HTTP_ACCEPT = 'HTTP_ACCEPT';
        const HTTP_ACCEPT_CHARSET = 'HTTP_ACCEPT_CHARSET';
        const HTTP_ACCEPT_ENCODING = 'HTTP_ACCEPT_ENCODING';
        const HTTP_ACCEPT_LANGUAGE = 'HTTP_ACCEPT_LANGUAGE';
        const HTTP_CONNECTION = 'HTTP_CONNECTION';
        const HTTP_HOST = 'HTTP_HOST';
        const HTTP_REFERER = 'HTTP_REFERER';
        const HTTP_USER_AGENT = 'HTTP_USER_AGENT';
        const HTTPS = 'HTTPS';
        const REMOTE_ADDR = 'REMOTE_ADDR';
        const REMOTE_HOST = 'REMOTE_HOST';
        const REMOTE_PORT = 'REMOTE_PORT';
        const REMOTE_USER = 'REMOTE_USER';
        const REDIRECT_REMOTE_USER = 'REDIRECT_REMOTE_USER';
        const SCRIPT_FILENAME = 'SCRIPT_FILENAME';
        const SERVER_ADMIN = 'SERVER_ADMIN';
        const SERVER_PORT = 'SERVER_PORT';
        const SERVER_SIGNATURE = 'SERVER_SIGNATURE';
        const PATH_TRANSLATED = 'PATH_TRANSLATED';
        const SCRIPT_NAME = 'SCRIPT_NAME';
        const REQUEST_URI = 'REQUEST_URI';
        const PHP_AUTH_DIGEST = 'PHP_AUTH_DIGEST';
        const PHP_AUTH_USER = 'PHP_AUTH_USER';
        const PHP_AUTH_PW = 'PHP_AUTH_PW';
        const AUTH_TYPE = 'AUTH_TYPE';
        const PATH_INFO = 'PATH_INFO';
        const PHP_SELF = 'PHP_SELF';
        const ORIG_PATH_INFO = 'ORIG_PATH_INFO';
    }
}