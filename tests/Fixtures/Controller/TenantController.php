<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Tests\Fixtures\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Fluxter\SaasProviderBundle\Controller\Abstraction\TenantClientController;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Entity\Note;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Repository\NoteRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

class TenantController extends TenantClientController
{
    #[Route('/tenant', name: 'tenant_show')]
    public function show(Environment $twig): Response
    {
        $template = $twig->createTemplate('{{ tenant_object.url }}|{{ tenant.url }}|{{ tenant.active ? "active" : "inactive" }}');

        return new Response($this->getTenant()->getUrl() . '|' . $template->render());
    }

    #[Route('/notes', name: 'tenant_notes')]
    public function notes(NoteRepository $notes, EntityManagerInterface $em): JsonResponse
    {
        // The tenant gets assigned by the TenantChildEntitySubscriber
        $em->persist(new Note('created in request'));
        $em->flush();

        return new JsonResponse(array_map(static fn (Note $note) => $note->getTitle(), $notes->findAll()));
    }

    #[Route('/public/health', name: 'public_health')]
    public function health(): Response
    {
        return new Response('ok');
    }
}
