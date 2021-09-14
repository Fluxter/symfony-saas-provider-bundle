# Additional Data on Tenant entity
If your entity has more fields, that have to be filled on creation (for example a name, email or something) you can use this event to fill this data (console only at the moment)
## Example listener
`src/EventSubscriber/TenantCreationSubscriber.php`
```php
<?php

namespace App\EventSubscriber;

use App\Entity\YourTenantEntity;
use Fluxter\SaasProviderBundle\Model\Event\ConsoleClientCreationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TenantCreationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ConsoleClientCreationEvent::class => 'onTenantCreation',
        ];
    }

    public function onTenantCreation(ConsoleClientCreationEvent $eventArgs): void
    {
        /** @var YourTenantEntity */
        $tenant = $eventArgs->getTenant();

        $email = $eventArgs->console()->ask('E-Mail');
        $tenant->setEmail($email);
    }
}
```