<?php

namespace Dcodegroup\ActivityLog\Contracts;

interface HasActivityUser
{
    public function getActivityLogUserName(): string;
}