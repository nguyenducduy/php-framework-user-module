<?php
namespace User\Service;

use Phalcon\Events\Event as PhEvent;
use Phalcon\Mvc\Dispatcher;
use Shirou\Service\Locator as ShServiceLocator;

class Authentication extends ShServiceLocator
{
    public function beforeDispatch(PhEvent $event, Dispatcher $dispatcher)
    {
        $request = $this->getDI()->get('request');
        $authManager = $this->getDI()->get('auth');

        $token = $request->getToken();

        if ($token) {
            $authManager->authenticateToken($token);
        }
    }
}
