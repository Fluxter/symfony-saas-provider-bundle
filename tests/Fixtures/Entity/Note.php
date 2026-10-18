<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fluxter\SaasProviderBundle\Model\TenantChildInterface;
use Fluxter\SaasProviderBundle\Model\TenantInterface;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Repository\NoteRepository;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
#[ORM\Table(name: 'note')]
class Note implements TenantChildInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Tenant::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tenant $tenant = null;

    #[ORM\ManyToOne(targetEntity: Note::class)]
    private ?Note $parent = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    public function __construct(string $title = '')
    {
        $this->title = $title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTenant(): ?TenantInterface
    {
        return $this->tenant;
    }

    public function setTenant(TenantInterface $client)
    {
        $this->tenant = $client;

        return $this;
    }

    public function getParent(): ?Note
    {
        return $this->parent;
    }

    public function setParent(?Note $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
