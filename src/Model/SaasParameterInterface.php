<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Model;

interface SaasParameterInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getValue(): string;

    public function setValue(string $value);
}
