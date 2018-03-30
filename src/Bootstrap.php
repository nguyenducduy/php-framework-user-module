<?php
namespace User;

use Phalcon\{
    DI,
    Events\Manager as PhEventsManager
};
use Shirou\Bootstrap as ShBootstrap;
use User\Plugin\Account\Email as UserEmailAccount;
use User\Session\JWT;
use User\Constants\AccountType;
use User\Session\Firebase\JWT\JWT as FirebaseJWT;
use User\Plugin\AuthManager as UserAuthManager;

class Bootstrap extends ShBootstrap
{
    protected $_moduleName = 'User';

    public function __construct(DI $di, PhEventsManager $em)
    {
        parent::__construct($di, $em);

        $di->set('auth', function () use ($di) {
            $sessionManager = new JWT(new FirebaseJWT());
            $authManager = new UserAuthManager($sessionManager);

            // 1. Instantiate Account Type
            $authEmail = new UserEmailAccount(AccountType::EMAIL);

            $authManager->setGenSalt(getenv('AUTH_SALT'));

            return $authManager
                ->addAccount(AccountType::EMAIL, $authEmail)
                ->setExpireTime(getenv('AUTH_EXPIRE'));
        }, true);

        $em->attach('init', $this);
    }

    public function afterEngine()
    {
        $di = $this->getDI();

        $this->getEventsManager()->attach('dispatch', $di->get('user')->authentication());
        $this->getEventsManager()->attach('dispatch', $di->get('user')->authorization());
        $this->getEventsManager()->attach('dispatch', $di->get('user')->tracking());
    }
}
