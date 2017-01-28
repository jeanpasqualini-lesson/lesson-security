<?php
namespace tests\Functionnal;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\Component\Security\Core\User\User;
use tests\Guard\QueryStringGuardAuthenticator;
use tests\TestKernel;

class SharedContextTest extends WebTestCase
{
    protected static function createKernel(array $options = array())
    {
        $environment = isset($options['environment']) ? $options['environment'] : 'sharedcontext';

        return new Class($environment) extends TestKernel  {
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
                        <?php echo User::class; ?>: plain
                    providers:
                        my_in_memory_provider:
                            memory:
                                users:
                                    john:
                                        password: gates
                                        roles: ROLE_USER
                    firewalls:
                        secure_part_1:
                            pattern: ^/secure/part1
                            context: secure_part1_part2_shared_same_context
                            anonymous: false
                            guard:
                                authenticators:
                                    - app.authenticator.query_string
                        secure_part_2:
                            pattern: ^/secure/part2
                            context: secure_part1_part2_shared_same_context
                            anonymous: false
                            guard:
                                authenticators:
                                    - app.authenticator.query_string

                        anonymous:
                            anonymous: true
                            pattern: ^/anonymous

                    access_control:
                        - { path: ^/secure/part1, roles: [ROLE_USER] }
                        - { path: ^/secure/part2, roles: [ROLE_USER] }

                services:
                    app.authenticator.query_string:
                        class: <?php echo QueryStringGuardAuthenticator::class.PHP_EOL; ?>
                        calls:
                            - [setUsernameField, ['_username']]
                            - [setPasswordField, ['_password']]
                <?php
            }

            protected function configureRoutes(RouteCollectionBuilder $routes)
            {
                $routes->add($path='/secure/part1/home', $controller='kernel:homeAction', $name='secure_part1_home');
                $routes->add($path='/secure/part2/home', $controller='kernel:homeAction', $name='secure_part2_home');
            }

            public function homeAction()
            {
                return new Response('home');
            }

            public function apiHomeAction()
            {
                return new JsonResponse(array(
                    'title' => 'home'
                ));
            }
        };
    }

    public function testSharedContext()
    {
        $client = static::createClient();

        // Authenticate in first firewall
        $client->request('GET', '/secure/part1/home?_username=john&_password=gates');

        // Also authenticate in second firewall thanks same context shared
        $client->request('GET', '/secure/part2/home');

        $this->assertEquals('home', $client->getResponse()->getContent());
    }
}