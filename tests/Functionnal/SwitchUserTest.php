<?php
namespace tests\Functionnal;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollectionBuilder;
use tests\Guard\QueryStringGuardAuthenticator;
use tests\TestKernel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class SwitchUserTest extends WebTestCase
{
    protected static function createKernel(array $options = array())
    {
        $environment = isset($options['environment']) ? $options['environment'] : 'switchuser';

        return new Class($environment) extends TestKernel
        {
            public function registerContainerConfigurationInternal()
            {
                ?>
                framework:
                    test: true
                    secret: 'mysecret'
                    session:
                        storage_id: session.storage.mock_file

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
                                    bill:
                                        roles: ['ROLE_USER']
                                        password: smith
                    firewalls:
                        main:
                            guard:
                                authenticators:
                                    - app.authenticator.query_string

                            switch_user:
                                role: ROLE_USER
                                provider: ~
                                parameter: _switch_user
                services:
                    app.authenticator.query_string:
                        class: <?php echo QueryStringGuardAuthenticator::class.PHP_EOL; ?>
                        calls:
                            - [setUsernameField, ['_username']]
                            - [setPasswordField, ['_password']]
                <?php
            }

            final protected function configureRoutes(RouteCollectionBuilder $routes) {
                $routes->add('/home', 'kernel:homeAction', 'home');
            }

            public function homeAction()
            {
                /** @var TokenStorage $tokenStorage */
                $tokenStorage = $this->getContainer()->get('security.token_storage');
                return new Response((string) $tokenStorage->getToken()->getUsername());
            }
        };
    }

    public function testSuccessfullSwitchUser()
    {
        $client = $this->createClient();
        $client->followRedirects();

        $client->request('GET', '/home?_username=john&_password=gates');

        $this->assertEquals('john', $client->getResponse()->getContent());

        $client->request('GET', '/home?_switch_user=bill');

        $this->assertEquals('bill', $client->getResponse()->getContent());
    }

    public function testFailedSwitchUser()
    {
        $this->setExpectedException(\LogicException::class);

        $client = $this->createClient();
        $client->followRedirects();

        $client->request('GET', '/home?_username=john&_password=gates');

        $this->assertEquals('john', $client->getResponse()->getContent());

        $client->request('GET', '/home?_switch_user=ball');
    }
}