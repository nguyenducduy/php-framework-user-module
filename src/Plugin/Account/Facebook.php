<?php
namespace User\Plugin\Account;

use Phalcon\Mvc\User\Plugin as PhPlugin;
use Shirou\Constants\ErrorCode;
use Shirou\UserException;
use User\Plugin\AccountInterface;
use User\Model\User as UserModel;
use Core\Helper\Utils as Helper;

class Facebook extends PhPlugin implements AccountInterface
{
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function login($email = null, $facebookObject = null)
    {
        $groups = $this->config->permission->groups;

        $avatarPath = Helper::downloadImage(
            $this->file,
            $facebookObject->oauthInfo->picture->data->url,
            $this->config->default->users->directory,
            $email
        );

        $myUser = UserModel::findFirst([
            'email = :email:',
            'bind' => [
                'email' => (string) $email
            ]
        ]);

        // User did not logged FB first, but has account
        if ($myUser && ($myUser->oauthprovider != 'facebook' || $myUser->oauthprovider == 'google')) {
            $myUser->assign([
                'oauthprovider' => 'facebook',
                'oauthaccesstoken' => (string) $facebookObject->oauthAccessToken,
                'oauthuid' => (int) $facebookObject->oauthUid
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
                'oauthprovider' => 'facebook',
                'oauthaccesstoken' => (string) $facebookObject->oauthAccessToken,
                'oauthuid' => (int) $facebookObject->oauthUid,
                'fullname' => (string) $facebookObject->oauthInfo->first_name . ' ' . $facebookObject->oauthInfo->last_name,
                'screenname' => (string) $facebookObject->oauthInfo->name,
                'gender' => (string) $facebookObject->oauthInfo->gender,
                // 'dob' => (int) strtotime($facebookObject->oauthInfo->birthday),
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
        } elseif ($myUser && $myUser->oauthprovider == 'facebook') {
            $myUser->oauthaccesstoken = (string) $facebookObject->oauthAccessToken;

            if (!$myUser->update()) {
                return false;
            }
        }

        return $myUser;
    }
}
