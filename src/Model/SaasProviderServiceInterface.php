<?php

namespace Fluxter\SaasProviderBundle\Model;

interface SaasProviderServiceInterface
{
    function createClient(): SaasClientInterface;

    function deactivateClient(): void;

    function deleteClient(): ClientInterface;

    function updateClient(): void;
}
