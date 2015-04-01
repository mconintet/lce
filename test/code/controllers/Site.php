<?php

namespace Lce\test\code\controllers;

use Lce\web\http\SessionManager;
use Lce\web\mvc\Controller;

class Site extends Controller
{
    public function indexAction()
    {
        echo __METHOD__;
        var_dump(SessionManager::getInstance()->get('test_session'));
        return;
    }

    /**
     * @return boolean return false to skip running real action
     */
    protected function _beforeDoActionMethod()
    {
        // TODO: Implement preAction() method.
    }
}