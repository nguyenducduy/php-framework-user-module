<?php
namespace User\Model;

use Core\Model\AbstractModel;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Shirou\Behavior\Model\Fileable;
use Core\Helper\Utils as Helper;

/**
 * @Source('fly_user');
 * @Behavior('\Shirou\Behavior\Model\Timestampable');
 */
class User extends AbstractModel
{
    /**
    * @Primary
    * @Identity
    * @Column(type="integer", nullable=false, column="u_id")
    */
    public $id;

    /**
    * @Column(type="string", nullable=true, column="u_screen_name")
    */
    public $screenname;

    /**
    * @Column(type="string", nullable=true, column="u_full_name")
    */
    public $fullname;

    /**
    * @Column(type="string", nullable=true, column="u_email")
    */
    public $email;

    /**
    * @Column(type="string", nullable=true, column="u_address")
    */
    public $address;

    /**
    * @Column(type="string", nullable=true, column="u_password")
    */
    public $password;

    /**
    * @Column(type="string", nullable=true, column="u_groupid")
    */
    public $groupid;

    /**
    * @Column(type="string", nullable=true, column="u_avatar")
    */
    public $avatar;

    /**
    * @Column(type="string", nullable=true, column="u_gender")
    */
    public $gender;

    /**
    * @Column(type="integer", nullable=true, column="u_status")
    */
    public $status;

    /**
    * @Column(type="integer", nullable=true, column="u_dob")
    */
    public $dob;

    /**
    * @Column(type="integer", nullable=true, column="u_oauth_uid")
    */
    public $oauthuid;

    /**
    * @Column(type="string", nullable=true, column="u_oauth_access_token")
    */
    public $oauthaccesstoken;

    /**
    * @Column(type="string", nullable=true, column="u_oauth_provider")
    */
    public $oauthprovider;

    /**
    * @Column(type="string", nullable=true, column="u_onesignal_id")
    */
    public $onesignalid;

    /**
    * @Column(type="integer", nullable=true, column="u_state")
    */
    public $state;

    /**
    * @Column(type="integer", nullable=true, column="u_date_created")
    */
    public $datecreated;

    /**
    * @Column(type="integer", nullable=true, column="u_date_last_change_password")
    */
    public $datelastchangepassword;

    /**
    * @Column(type="integer", nullable=true, column="u_date_modified")
    */
    public $datemodified;

    /**
    * @Column(type="string", nullable=true, column="u_mobile_number")
    */
    public $mobilenumber;

    /**
    * @Column(type="integer", nullable=true, column="u_is_verified")
    */
    public $isverified;

    /**
    * @Column(type="integer", nullable=true, column="u_verify_type")
    */
    public $verifytype;

    const STATUS_ENABLE = 1;
    const STATUS_DISABLE = 3;
    const VERIFY_TYPE_EMAIL = 1;
    const VERIFY_TYPE_PHONE = 3;
    const IS_VERIFIED = 1;
    const IS_NOT_VERIFIED = 3;

    /**
     * Initialize model
     */
    public function initialize()
    {
        $config = $this->getDI()->get('config');

        if (!$this->getDI()->get('app')->isConsole()) {
            $configBehavior = [
                'field' => 'avatar',
                'uploadPath' => $config->default->users->directory,
                'allowedFormats' => $config->default->users->mimes->toArray(),
                'allowedMaximumSize' => $config->default->users->maxsize,
                'allowedMinimumSize' => $config->default->users->minsize,
                'isOverwrite' => $config->default->users->isoverwrite
            ];

            $this->addBehavior(new Fileable([
                'beforeDelete' => $configBehavior
            ]));
        }
    }

    /**
     * Form field validation
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add('groupid', new PresenceOf([
            'message' => 'message-groupid-notempty'
        ]));

        $validator->add('status', new PresenceOf([
            'message' => 'message-status-notempty'
        ]));

        $validator->add('email', new Uniqueness([
            'message' => 'message-email-unique'
        ]));

        return $this->validate($validator);
    }

    public function getStatusName(): string
    {
        $name = '';
        $lang = self::getStaticDi()->get('lang');

        switch ($this->status) {
            case self::STATUS_ENABLE:
                $name = $lang->_('label-status-enable');
                break;
            case self::STATUS_DISABLE:
                $name = $lang->_('label-status-disable');
                break;
        }

        return $name;
    }

    public static function getStatusList()
    {
        $lang = self::getStaticDi()->get('lang');

        return $data = [
            [
                'label' => $lang->_('label-status-enable'),
                'value' => (string) self::STATUS_ENABLE
            ],
            [
                'label' => $lang->_('label-status-disable'),
                'value' => (string) self::STATUS_DISABLE
            ],
        ];
    }

    public function getVerifyName(): string
    {
        $name = '';
        $lang = self::getStaticDi()->get('lang');

        switch ($this->isverified) {
            case self::IS_VERIFIED:
                $name = $lang->_('label-is-verified');
                break;
            case self::IS_NOT_VERIFIED:
                $name = $lang->_('label-is-not-verified');
                break;
        }

        return $name;
    }

    public function getVerifyStyle(): string
    {
        $name = '';

        switch ($this->isverified) {
            case self::IS_VERIFIED:
                $name = 'primary';
                break;
            case self::IS_NOT_VERIFIED:
                $name = 'danger';
                break;
        }

        return $name;
    }

    public static function getVerifyList()
    {
        $lang = self::getStaticDi()->get('lang');

        return $data = [
            [
                'label' => $lang->_('label-verify-type-email'),
                'value' => (string) self::VERIFY_TYPE_EMAIL
            ],
            [
                'label' => $lang->_('label-verify-type-phone'),
                'value' => (string) self::VERIFY_TYPE_PHONE
            ],
        ];
    }

    /**
     * Get label style for status
     */
    public function getStatusStyle(): string
    {
        $class = '';
        switch ($this->status) {
            case self::STATUS_ENABLE:
                $class = 'primary';
                break;
            case self::STATUS_DISABLE:
                $class = 'danger';
                break;
        }

        return $class;
    }

    public function getVerifyTypeName(): string
    {
        $name = '';

        switch ($this->verifytype) {
            case self::VERIFY_TYPE_PHONE:
                $name = 'phone';
                break;
            case self::VERIFY_TYPE_EMAIL:
                $name = 'email';
                break;
        }

        return $name;
    }

    public static function getGroupList()
    {
        $config = self::getStaticDi()->get('config');
        $groups = array_keys($config->acl->groups->toArray());

        // remove 2 default groups
        unset($groups[0]);
        unset($groups[1]);

        return $groups;
    }

    /**
     * Validate password
     */
    public function validatePassword($password): string
    {
        return $this->getDI()->get('security')->checkHash($password, $this->password);
    }

    // return avatar response support api
    public function getAvatarJson(): string
    {
        $config = $this->getDI()->get('config');
        $url = $this->getDI()->get('url');

        if ($this->avatar != '') {
            return Helper::getFileUrl(
                $url->getBaseUri(),
                $config->default->users->directory,
                $this->avatar
            );
        } else {
            return '';
        }
    }
}
