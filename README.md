# SaaS Provider bundle

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Fluxter/SaasProviderBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Fluxter/SaasProviderBundle/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/Fluxter/SaasProviderBundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Fluxter/SaasProviderBundle/build-status/master)

The corresponding provider bundle for the Fluxter SaaS Consumer bundle

## How to install

```bash
composer require fluxter/saas-provider-bundle
```

## How to implement

1. Create a client entity, that implements the SaasClientInterface
1. Create a Client Parameter entity, that implements SaasClientParameterInterface
1. Configure the bundle
    ```yaml
    # config/packages/saas_provider.yaml
    saas_provider:
      client_entity: App\Entity\User
      global_url: app.yourdomain.tld
      apikey: <An unused api key>
    ```
1. Configure your firewall to the correct voter mode
    ```yaml
    # config/packages/security.yaml
    security:
        access_decision_manager:
            strategy: unanimous
            allow_if_all_abstain: false
    ```

