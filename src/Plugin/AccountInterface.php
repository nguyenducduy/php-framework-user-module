<?php
namespace User\Plugin;

interface AccountInterface
{
    public function login($email = null, $password = null);
}
