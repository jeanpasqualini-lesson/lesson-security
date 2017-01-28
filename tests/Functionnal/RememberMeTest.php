<?php

namespace tests\Functionnal;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\Component\Security\Core\User\User;
use tests\Guard\QueryStringGuardAuthenticator;
use tests\TestKernel;

class RememberMeTest extends WebTestCase
{
    protected static function createKernel(array $options = array())
    {
        $environment = isset($options['environment']) ? $options['environment'] : 'rememberme';

        return new Class($environment) extends TestKernel   {
            public function registerContainerConfigurationInternal()
            {
                ?>
                framework:
                    test: true
                    secret: 'mysecret'

                security:
                    encoders:
                        <?php echo User::class; ?>: plain
                    providers:
                        my_in_memory_provider:
                            memory:
                                users:
                                    john:
                                        password: gates
                                        roles: ROLE_USER
                    firewalls:
                        secure:
                            pattern: ^/secure
                            anonymous: false
                            guard:
                                authenticators:
                                    - app.authenticator.query_string
                            remember_me:
                                always_remember_me: true
                                secret: mysecret
                                lifetime: 604800
                                path: /secure

                    access_control:
                        - { path: ^/secure, roles: [ROLE_USER] }

                services:
                    app.authenticator.query_string:
                        class: <?php echo QueryStringGuardAuthenticator::class.PHP_EOL; ?>
                        calls:
                            - [activeRememberMe, []]
                            - [setUsernameField, ['_username']]
                            - [setPasswordField, ['_password']]
                <?php
            }

            protected function configureRoutes(RouteCollectionBuilder $routes)
            {
                $routes->add($path='/secure/home', $controller='kernel:homeAction', $name='secure_home');
            }

            public function homeAction()
            {
                return new Response('home');
            }
        };
    }

    public function testRememberMe()
    {
        $client = static::createClient();

        # First step : login

        $client->request('GET', '/secure/home?_username=john&_password=gates');

        $this->assertEquals('', $client->getResponse()->getContent());


        # Two step : logout token [DELETED], remember me [SAVED]

        $client->getContainer()->get('security.token_storage')->setToken(null);


        # Three step : autologin with rememberme cookie

        $client->request('GET', '/secure/home');

        $this->assertEquals('home', $client->getResponse()->getContent());


        # Foor step : logout token [DELETED], remember me [DELETED]

        $client->getContainer()->get('security.token_storage')->setToken(null);

        $client->getCookieJar()->expire('REMEMBERME', '/secure');

        # Five steap : try access secure page

        $client->request('GET', '/secure/home');

        $this->assertEquals(
            Response::HTTP_UNAUTHORIZED,
            $client->getResponse()->getStatusCode(),
            $client->getResponse()->getContent()
        );
    }
}