<?php
namespace tests\Functionnal;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollectionBuilder;
use tests\EventListener\RequestListener;
use tests\Guard\QueryStringGuardAuthenticator;
use tests\Provider\FileProvider;
use tests\TestKernel;

class ProviderTest extends WebTestCase
{
    // MyListener : onKernelRequest = 255
    // Routing  : onKernelRequest = 25
    // Firewall : onKernelRequest = 8

    protected static function createKernel(array $options = array())
    {
        $environment = isset($options['environment']) ? $options['environment'] : 'provider';

        return new Class($environment) extends TestKernel
        {
            public function registerContainerConfigurationInternal()
            {
                ?>
                framework:
                    test: true
                    secret: 'mysecret'

                security:
                    encoders:
                        truc: test

                    providers:
                        my_memory_provider:
                            memory:
                                users:
                                    john:
                                        roles: ['ROLE_USER']
                                        password: gates
                #        my_entity_provider:
                #            entity:
                #                class: Truc
                #                property: username
                #                manager_name: ~
                        my_custom_provider:
                            id: app.user_provider.file
                    firewalls:
                        use_memory_provider:
                            pattern: ^/security/use_memory_provider
                            provider: my_memory_provider
                            guard:
                                authenticators:
                                    - app.authenticator.query_string
                        use_entity_provider:
                            pattern: ^/security/use_entity_provider
                            guard:
                                authenticators:
                                    - app.authenticator.query_string
                        use_custom_provider:
                            pattern: ^/security/use_custom_provider
                            provider: my_custom_provider
                            guard:
                                authenticators:
                                    - app.authenticator.query_string
                services:
                    app.authenticator.query_string:
                        class: <?php echo QueryStringGuardAuthenticator::class.PHP_EOL; ?>
                        calls:
                            - [setUsernameField, ['_username']]
                            - [setPasswordField, ['_password']]
                    app.event_listener.request_listener:
                        class: <?php echo RequestListener::class.PHP_EOL; ?>
                        arguments: ['@event_dispatcher']
                        tags:
                            -
                                name: kernel.event_listener
                                event: kernel.request
                                method: onKernelRequest
                                priority: 1
                    app.user_provider.file:
                        class: <?php echo FileProvider::class.PHP_EOL; ?>
                        arguments: ["%kernel.root_dir%/../Fixture/username.password"]
                <?php
            }

            final protected function configureRoutes(RouteCollectionBuilder $routes) { }

            public function registerContainerConfigurationRouting(LoaderInterface $loader)
            {
                $loader->load(function (ContainerBuilder $container) use ($loader) {
                    $container->setParameter('request_listener.http_port', 80);
                    $container->setParameter('request_listener.https_port', 443);

                    $container->addObjectResource($this);
                });
            }
        };
    }

    public function testInMemoryProvider()
    {
        $client = static::createClient();

        $client->request('GET', '/security/use_memory_provider/home?_username=john&_password=gates');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $client->request('GET', '/security/use_memory_provider/home');

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    public function testEntityProvider()
    {
        $client = static::createClient();

        $client->request('GET', '/security/use_entity_provider/home?_username=john&_password=gates');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $client->request('GET', '/security/use_entity_provider/home');

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    public function testCustomProvider()
    {
        $client = static::createClient();

        $client->request('GET', '/security/use_custom_provider/home?_username=jules&_password=vernes');

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $client->request('GET', '/security/use_custom_provider/home');

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }
}