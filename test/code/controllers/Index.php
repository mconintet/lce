<?php

namespace Lce\test\code\controllers;


use Lce\web\mvc\Controller;

class Index extends Controller
{
    public function indexAction()
    {
        //var_dump($this->getParam('id'));
        echo 'Hello World';

//        $view = new TestView();
//        $view->setTemplate('test.phtml');
//        $view->render();

        //$this->redirect('site/index');
        //throw new \Exception('test');
//
//        EventManager::getInstance()->dispatch('test');
//
//        CookeManager::getInstance()->set('test', 1);
//
//        SessionManager::getInstance()->set('test_session', 'session');

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