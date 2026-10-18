<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Security;

use Fluxter\SaasProviderBundle\Model\TenantChildInterface;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TenantChildVoter extends Voter
{
    private TenantService $_clientService;

    public function __construct(TenantService $clientService)
    {
        $this->_clientService = $clientService;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!$subject instanceof TenantChildInterface) {
            return false;
        }

        return true;
    }

    /**
     * @param TenantChildInterface $subject
     * @param Vote|null            $vote    only passed since Symfony 7.3
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
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
