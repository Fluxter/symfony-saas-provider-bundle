<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use Fluxter\SaasProviderBundle\Entity\AbstractSaasClient;
use Fluxter\SaasProviderBundle\Model\TenantChildInterface;

#[ORM\Entity]
#[ORM\Table(name: 'tenant')]
class Tenant extends AbstractSaasClient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $url = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    /** @var TenantChildInterface[] */
    private array $parameters = [];

    public function __construct(?string $url = null)
    {
        $this->url = $url;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Only for unit tests, where no entity manager assigns ids.
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function addParameter(TenantChildInterface $parameter)
    {
        $this->parameters[] = $parameter;
    }

    /**
     * @return TenantChildInterface[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
