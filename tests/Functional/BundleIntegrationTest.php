<?php

/*
 * This file is part of the SaasProviderBundle package.
 * (c) Fluxter <http://fluxter.net/>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fluxter\SaasProviderBundle\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Fluxter\SaasProviderBundle\Form\Type\TenantChildType;
use Fluxter\SaasProviderBundle\Form\TypeGuesser\TenantChildEntityTypeGuesser;
use Fluxter\SaasProviderBundle\Service\DynamicSaasClientAccessorService;
use Fluxter\SaasProviderBundle\Service\TenantService;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Entity\Note;
use Fluxter\SaasProviderBundle\Tests\Fixtures\Entity\Tenant;
use Fluxter\SaasProviderBundle\Tests\Fixtures\TestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Boots a real Symfony application (FrameworkBundle, TwigBundle, DoctrineBundle with SQLite)
 * with the bundle installed.
 */
class BundleIntegrationTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private Tenant $tenantA;
    private Tenant $tenantB;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        (new SchemaTool($this->em))->createSchema($this->em->getMetadataFactory()->getAllMetadata());

        $this->tenantA = new Tenant('tenant-a.example.com');
        $this->tenantB = new Tenant('tenant-b.example.com');
        $this->em->persist($this->tenantA);
        $this->em->persist($this->tenantB);
        $this->em->persist((new Note('note of a'))->setTenant($this->tenantA));
        $this->em->persist((new Note('note of b'))->setTenant($this->tenantB));
        $this->em->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // The FrameworkBundle registers the Symfony ErrorHandler on boot
        restore_exception_handler();
    }

    public function testPublicServices(): void
    {
        $container = self::$kernel->getContainer();

        $this->assertInstanceOf(TenantService::class, $container->get(TenantService::class));
        $this->assertInstanceOf(DynamicSaasClientAccessorService::class, $container->get(DynamicSaasClientAccessorService::class));
    }

    public function testRequestResolvesTenantByHost(): void
    {
        $response = $this->request('https://Tenant-A.example.com:8443/tenant');

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        $this->assertSame('tenant-a.example.com|tenant-a.example.com|tenant-a.example.com|active', $response->getContent());
    }

    public function testUnknownHostIsNotFound(): void
    {
        $response = $this->request('http://unknown.example.com/tenant');

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testExcludedRouteWorksWithoutTenant(): void
    {
        $response = $this->request('http://unknown.example.com/public/health');

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        $this->assertSame('ok', $response->getContent());
    }

    public function testGlobalUrlWorksWithoutTenant(): void
    {
        $response = $this->request('http://www.example.com/tenant-optional');

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        $this->assertSame('no tenant', $response->getContent());
    }

    public function testRepositoryFindMethodsAreFilteredByTenant(): void
    {
        $repository = $this->em->getRepository(Note::class);
        self::getContainer()->get('request_stack')->push(Request::create('http://tenant-a.example.com/'));

        $this->assertSame(['note of a'], array_map(static fn (Note $note) => $note->getTitle(), $repository->findBy([])));
        $this->assertNull($repository->findOneBy(['title' => 'note of b']));
        $this->assertSame('note of a', $repository->findOneBy(['title' => 'note of a'])?->getTitle());

        $noteOfB = $repository->createGlobalQueryBuilder('n')->andWhere("n.title = 'note of b'")->getQuery()->getSingleResult();
        $this->assertNull($repository->find($noteOfB->getId()));
        $this->assertSame('note of b', $repository->findOneBy(['title' => 'note of b'], null, $this->tenantB)?->getTitle());
    }

    public function testRepositoryIsFilteredByTenantAndNewChildrenGetTheTenant(): void
    {
        $response = $this->request('http://tenant-b.example.com/notes');

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode(), (string) $response->getContent());
        $this->assertSame(['note of b', 'created in request'], json_decode($response->getContent(), true));

        $this->em->clear();
        $created = $this->em->getRepository(Note::class)->createGlobalQueryBuilder('n')
            ->andWhere('n.title = :title')
            ->setParameter('title', 'created in request')
            ->getQuery()
            ->getSingleResult();
        $this->assertSame($this->tenantB->getId(), $created->getTenant()->getId());
    }

    public function testCreateClientCommand(): void
    {
        $application = new Application(self::$kernel);
        $tester = new CommandTester($application->find('fluxter:saas:provider:create-client'));
        $tester->execute(['url' => 'tenant-c.example.com']);

        $tester->assertCommandIsSuccessful();
        $this->em->clear();
        $this->assertNotNull($this->em->getRepository(Tenant::class)->findOneBy(['url' => 'tenant-c.example.com']));
    }

    public function testTypeGuesserUsesTenantChildTypeForTenantChildAssociations(): void
    {
        $guesser = self::getContainer()->get(TenantChildEntityTypeGuesser::class);

        $guess = $guesser->guessType(Note::class, 'parent');
        $this->assertNotNull($guess);
        $this->assertSame(TenantChildType::class, $guess->getType());
        $this->assertSame(['em' => 'default', 'class' => Note::class, 'multiple' => false], $guess->getOptions());
        $this->assertSame(Guess::HIGH_CONFIDENCE, $guess->getConfidence());

        // The tenant itself is no tenant child
        $this->assertNull($guesser->guessType(Note::class, 'tenant'));
        $this->assertNull($guesser->guessType(Note::class, 'title'));
        $this->assertNull($guesser->guessType(\stdClass::class, 'foo'));
    }

    public function testTenantChildFormTypeOnlyListsChildrenOfTheCurrentTenant(): void
    {
        // The choices are loaded through the AbstractTenantRepository
        self::getContainer()->get('request_stack')->push(Request::create('http://tenant-a.example.com/'));

        $form = self::getContainer()->get(FormFactoryInterface::class)
            ->createBuilder(FormType::class, new Note())
            ->add('parent', TenantChildType::class, ['class' => Note::class, 'choice_label' => 'title'])
            ->getForm();

        $this->assertInstanceOf(TenantChildType::class, $form->get('parent')->getConfig()->getType()->getInnerType());
        $this->assertSame(
            ['note of a'],
            array_map(static fn ($choice) => $choice->label, array_values($form->get('parent')->createView()->vars['choices'])),
        );
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    private function request(string $uri): Response
    {
        return self::$kernel->handle(Request::create($uri));
    }
}
