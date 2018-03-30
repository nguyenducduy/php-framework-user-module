<?php
namespace User\Plugin;

interface ISession
{
    public function encode($token);
    public function decode($token);
    public function create($issuer, $user, $iat, $exp);
}
