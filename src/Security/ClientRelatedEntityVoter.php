<?php

/*
 * This file is part of the ClanManager package.
 * (c) Fluxter <https://fluxter.net/>
 * Found us at <https://clanmanager.net>
 */

namespace Fluxter\SaasProviderBundle\Security;

use Fluxter\SaasProviderBundle\Model\SaasClientRelatedInterface;
use Fluxter\SaasProviderBundle\Service\SaasClientService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ClientRelatedEntityVoter extends Voter
{
    private SaasClientService $_clientService;

    public function __construct(SaasClientService $clientService)
    {
        $this->_clientService = $clientService;
    }

    protected function supports($attribute, $subject)
    {
        if (!$subject instanceof SaasClientRelatedInterface) {
            return false;
        }

        return true;
    }

    /**
     * @param string                     $attribute
     * @param SaasClientRelatedInterface $subject
     *
     * @return void
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$this->_clientService->getCurrentClient()) {
            return false;
        }

        if ($subject->getClient() == null) {
            return true;
        }

        if ($this->_clientService->getCurrentClient() == $subject->getClient()) {
            return true;
        }

        return false;
    }
}
