# SaaS Provider bundle

[![CI](https://github.com/Fluxter/symfony-saas-provider-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/Fluxter/symfony-saas-provider-bundle/actions/workflows/ci.yml)

The corresponding provider bundle for the Fluxter SaaS Consumer bundle

## Requirements

- PHP 8.2 or newer (Symfony 8 requires PHP 8.4)
- Symfony 7 or 8
- Doctrine ORM 2.17+ / 3 with DoctrineBundle 2.11+ / 3

## How to install

```bash
composer require fluxter/saas-provider-bundle
```

## How to implement

1. Create a client entity, that implements the TenantInterface
1. Create a Client Parameter entity, that implements SaasClientParameterInterface
1. Configure the bundle
    ```yaml
    # config/packages/saas_provider.yaml
    saas_provider:
      client_entity: App\Entity\Tenant
      # Optional: host that works without a tenant (e.g. the landing page / sign up)
      global_url: app.yourdomain.tld
      # Optional: regular expressions of routes that also work without a tenant
      exclude_routes:
        - '^_profiler'
        - '^_wdt'
    ```
1. Configure your firewall to the correct voter mode
    ```yaml
    # config/packages/security.yaml
    security:
        access_decision_manager:
            strategy: unanimous
            allow_if_all_abstain: false
    ```

## Development

Everything runs inside the dev container (`.devcontainer/`), so only Docker is needed, no local PHP.
You can also open the project in VS Code with the "Dev Containers" extension.

```bash
make help                          # list all targets
make install                       # composer install
make tests                         # run the PHPUnit test suite
make composer c="require foo/bar"  # run any composer command
make shell                         # open a shell in the container
make lint                          # check coding standards (make fix to fix them)
```

Test against a specific PHP / Symfony version (like the GitHub Actions matrix does):

```bash
make update tests PHP_VERSION=8.2 SYMFONY=7.4
make matrix   # every PHP / Symfony combination
make lowest   # lowest supported dependencies
```
