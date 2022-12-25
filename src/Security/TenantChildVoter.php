<?php

/*
 * (c) Fluxter <http://fluxter.net/>
 */

namespace Fluxter\SaasProviderBundle\Security;

use Fluxter\SaasProviderBundle\Model\TenantChildInterface;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TenantChildVoter extends Voter
{
    private TenantService $_clientService;

    public function __construct(TenantService $clientService)
    {
        $this->_clientService = $clientService;
    }

    protected function supports($attribute, $subject): bool
    {
        if (!$subject instanceof TenantChildInterface) {
            return false;
        }

        return true;
    }

    /**
     * @param string                     $attribute
     * @param TenantChildInterface $subject
     *
     * @return void
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$this->_clientService->getTenant()) {
            return false;
        }

        if (null == $subject->getTenant()) {
            return true;
        }

        if ($this->_clientService->getTenant() == $subject->getTenant()) {
            return true;
        }

        return false;
    }
}
