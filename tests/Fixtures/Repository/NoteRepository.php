<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Tests\Fixtures\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Fluxter\SaasProviderBundle\Repository\Abstraction\AbstractTenantRepository;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Entity\Note;

class NoteRepository extends AbstractTenantRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }
}
