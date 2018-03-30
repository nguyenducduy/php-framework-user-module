<?php
namespace User\Plugin;

use Phalcon\Mvc\User\Plugin as PhPlugin;
use Shirou\Constants\ErrorCode;
use Shirou\UserException;

use User\Plugin\Account\Email as UserEmailAccount;
use User\Model\User as UserModel;
use User\Constants\ErrorCode as UserErrorCode;

class AuthManager extends PhPlugin
{
    protected $user;
    protected $issuer;
    protected $expireTime;
    protected $accounts;
    protected $token;
    protected $genSalt;

    public function __construct(ISession $sessionManager)
    {
        $this->issuer = null;
        $this->expireTime = 86400 * 365; // Default one week
        $this->accounts = [];
        $this->sessionManager = $sessionManager;

        return $this;
    }

    public function setGenSalt($salt)
    {
        $this->genSalt = $salt;
    }

    public function addAccount($name, $account)
    {
        $this->accounts[$name] = $account;

        return $this;
    }

    public function getAccounts()
    {
        return $this->accounts;
    }

    public function setExpireTime($time)
    {
        $this->expireTime = $time;

        return $this;
    }

    public function getExpireTime()
    {
        return $this->expireTime;
    }

    public function setIssuer($issuer)
    {
        $this->issuer = $issuer;

        return $issuer;
    }

    public function getIssuer()
    {
        return $this->issuer;
    }

    public function setSessionManager($session)
    {
        $this->sessionManager = $session;

        return $this;
    }

    public function getSessionManager()
    {
        return $this->sessionManager;
    }

    /**
     * Set user model
     * @param [object] $user User\Model\User
     */
    public function setUser($user)
    {
        // Hide password in jwt token
        $user->password = '';

        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function loggedIn()
    {
        return !!$this->user;
    }

    public function getAccount($name)
    {
        if (array_key_exists($name, $this->accounts)) {
            return $this->accounts[$name];
        }

        return false;
    }

    public function login($bearer, $email, $password)
    {
        $this->setIssuer($bearer);

        if (!$account = $this->getAccount($bearer)) {
            throw new UserException(UserErrorCode::AUTH_INVALIDTYPE);
        }

        $user = $account->login($email, $password);

        if (!$user) {
            throw new UserException(UserErrorCode::AUTH_BADLOGIN);
        }

        $user->avatar = $user->getAvatarJson();

        $this->setUser($user);

        return $this;
    }

    public function getToken($key = null)
    {
        if (!$this->token) {
            $this->token = $this->sessionManager->create($this->getIssuer(), $this->getUser(), time(), $this->getExpireTime());
        }

        if ($key) {
            return $this->token[$key];
        }

        return $this->token;
    }

    public function hasToken()
    {
        return !!$this->token;
    }

    public function authenticateToken($token)
    {
        try {
            $decoded = $this->sessionManager->decode($token);
            if ($decoded->sub->status != UserModel::STATUS_ENABLE) {
                throw new UserException(UserErrorCode::AUTH_EXPIRED);
            }
        } catch (\UnexpectedValueException $e) {
            $this->getDI()->get('response')->sendException($e);
            return false;
        }

        // Set session
        if ($decoded && $decoded->exp > time()) {
            $this->setUser($decoded->sub);
        }

        return true;
    }

    public function getTokenResponse()
    {
        return [
            'AuthToken' => $this->sessionManager->encode($this->getToken()),
            'Expires' => $this->getToken('exp'),
            'AccountType' => $this->getIssuer()
        ];
    }
}
