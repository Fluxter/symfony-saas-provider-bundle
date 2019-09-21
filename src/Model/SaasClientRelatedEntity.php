<?php

namespace Fluxter\SaasProviderBundle\Model;

interface SaasClientRelatedEntity
{
    public function getClient(): SaasClientInterface;

    public function setClient(SaasClientInterface $client);
}
