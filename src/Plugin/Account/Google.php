<?php
namespace User\Plugin\Account;

use Phalcon\Mvc\User\Plugin as PhPlugin;
use Shirou\Constants\ErrorCode;
use Shirou\UserException;
use User\Plugin\AccountInterface;
use User\Model\User as UserModel;
use Core\Helper\Utils as Helper;

class Google extends PhPlugin implements AccountInterface
{
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function login($email = null, $googleObject = null)
    {
        $groups = $this->config->permission->groups;

        $avatarPath = Helper::downloadImage(
            $this->file,
            $googleObject->oauthInfo->avatar,
            $this->config->default->users->directory,
            $email
        );

        $myUser = UserModel::findFirst([
            'email = :email:',
            'bind' => [
                'email' => (string) $email
            ]
        ]);

        // User did not logged GG first, but has account
        if ($myUser && ($myUser->oauthprovider != 'google' || $myUser->oauthprovider == 'facebook')) {
            $myUser->assign([
                'oauthprovider' => 'google',
                'oauthaccesstoken' => (string) $googleObject->oauthAccessToken,
                'oauthuid' => (int) $googleObject->oauthUid
            ]);

            if (!$myUser->update()) {
                return false;
            }
        } elseif (!$myUser) {
            $myUser = new UserModel();
            $myUser->assign([
                'email' => (string) $email,
                'groupid' => $groups->defaultOauth,
                'status' => UserModel::STATUS_ENABLE,
                'oauthprovider' => 'google',
                'oauthaccesstoken' => (string) $googleObject->oauthAccessToken,
                'oauthuid' => (int) $googleObject->oauthUid,
                'fullname' => (string) $googleObject->oauthInfo->name,
                'screenname' => (string) $googleObject->oauthInfo->name,
                'password' => $this->security->hash('!mtPosi*73,###&79'),
                'avatar' => $avatarPath,
                'isverified' => UserModel::IS_VERIFIED,
                'verifytype' => UserModel::VERIFY_TYPE_EMAIL
            ]);

            if (!$myUser->create()) {
                return false;
            }

            // Add points
            $myEvents = EventModel::find([
                'status = :status:',
                'bind' => [
                    'status' => EventModel::STATUS_ENABLE
                ]
            ]);

            if (count($myEvents) > 0) {
                foreach ($myEvents as $event) {
                    $myRelUserEvent = new RelUserEventModel();
                    $myRelUserEvent->assign([
                        'uid' => (int) $myUser->id,
                        'eid' => (int) $event->id,
                        'count' => (int) $event->maxaccumulation
                    ]);

                    if (!$myRelUserEvent->create()) {
                        throw new UserException(ErrorCode::DATA_CREATE_FAIL);
                    }
                }
            }

            $myGoogle = $myUser;
        } elseif ($myUser && $myUser->oauthprovider == 'google') {
            $myUser->oauthaccesstoken = (string) $googleObject->oauthAccessToken;

            if (!$myUser->update()) {
                return false;
            }
        }

        return $myUser;
    }
}
