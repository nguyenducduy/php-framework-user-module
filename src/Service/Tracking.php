<?php
namespace User\Service;

use Phalcon\Events\Event as PhEvent;
use Phalcon\Mvc\Dispatcher;
use Shirou\Service\Locator as ShServiceLocator;
use Jenssegers\Agent\Agent;
use User\Model\UserDevices as UserDevicesModel;

class Tracking extends ShServiceLocator
{
    public function beforeDispatch(PhEvent $event, Dispatcher $dispatcher)
    {
        $uid = 0;
        $token = $this->getDI()->get('request')->getToken();
        $agent = new Agent();

        if ($token) {
            $uid = $this->getDI()->get('auth')->getUser()->id;
        }

        $myUserDevices = new UserDevicesModel();
        $myUserDevices->assign([
            'uid' => (int) $uid,
            'platform' => (string) $agent->platform(),
            'device' => (string) $agent->device(),
            'browser' => (string) $agent->browser(),
            'useragent' => (string) $agent->getUserAgent(),
            'ipaddress' => (string) ip2long($_SERVER['REMOTE_ADDR']),
            'version' => (string) $agent->version($agent->browser()),
            'isrobot' => (int) $agent->isRobot()
        ]);

        $myUserDevices->create();
    }
}
