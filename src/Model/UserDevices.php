<?php
namespace User\Model;

use Core\Model\AbstractModel;

/**
 * @Source('fly_user_devices');
 * @Behavior('\Shirou\Behavior\Model\Timestampable');
 */
class UserDevices extends AbstractModel
{
    /**
    * @Primary
    * @Identity
    * @Column(type="integer", nullable=false, column="ud_id")
    */
    public $id;

    /**
    * @Column(type="integer", nullable=true, column="u_id")
    */
    public $uid;

    /**
    * @Column(type="string", nullable=true, column="ud_platform")
    */
    public $platform;

    /**
    * @Column(type="string", nullable=true, column="ud_device")
    */
    public $device;

    /**
    * @Column(type="string", nullable=true, column="ud_browser")
    */
    public $browser;

    /**
    * @Column(type="string", nullable=true, column="ud_version")
    */
    public $version;

    /**
    * @Column(type="string", nullable=true, column="ud_user_agent")
    */
    public $useragent;

    /**
    * @Column(type="integer", nullable=true, column="ud_ip_address")
    */
    public $ipaddress;

    /**
    * @Column(type="integer", nullable=true, column="ud_is_robot")
    */
    public $isrobot;

    /**
    * @Column(type="integer", nullable=true, column="ud_date_created")
    */
    public $datecreated;

    /**
    * @Column(type="integer", nullable=true, column="ud_date_modified")
    */
    public $datemodified;

}
