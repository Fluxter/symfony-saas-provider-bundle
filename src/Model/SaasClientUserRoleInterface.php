<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Model;

interface SaasClientUserRoleInterface
{
    public function getRoles();

    public function addRole($role);

    public function getUser(): SaasClientUserInterface;
}
