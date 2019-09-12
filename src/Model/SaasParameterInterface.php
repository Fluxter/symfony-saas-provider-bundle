<?php

namespace Fluxter\SaasProviderBundle\Model;

interface SaasParameterInterface
{
    function getName(): string;

    function setName(string $name): void;

    function getValue(): string;

    function setValue(string $value);
}
