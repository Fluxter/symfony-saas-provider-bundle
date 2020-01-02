<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Model;

interface SaasClientInterface
{
    public function getId();

    public function getSaasParameters(): array;

    public function getUrl();

    public function addParameter(SaasParameterInterface $parameter);

    public function removeParameter(SaasParameterInterface $parameter);
}
