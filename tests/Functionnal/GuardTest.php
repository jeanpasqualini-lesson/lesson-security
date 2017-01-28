<?php
namespace tests\Functionnal;

use GuardAuthenticator\StaticGuardAuthenticator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Symfony\Component\Security\Core\User\User;
use tests\Guard\QueryStringGuardAuthenticator;
use tests\TestKernel;

/**
 * GuardTest
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 * @package tests;
 */
class GuardTest extends WebTestCase
{
    protected static function createKernel(array $options = array())
    {
        $environment = isset($options['environment']) ? $options['environment'] : 'guard';

        return new Class($environment) extends TestKernel {
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
                        admin:
                            pattern: ^/(user|admin)
                            anonymous: false
                            guard:
                                authenticators:
                                    - app.authenticator.static
                        mobile:
                            stateless: true
                            pattern: ^/api
                            anonymous: false
                            guard:
                                authenticators:
                                    - app.authenticator.query_string

                        anonymous:
                            anonymous: true
                            pattern: ^/anonymous

                    access_control:
                        - { path: ^/user/home, roles: [ROLE_USER] }
                        - { path: ^/admin/home, roles: [ROLE_ADMIN] }
                        - { path: ^/api, roles: [ROLE_USER] }

                services:
                    app.authenticator.static:
                        class: <?php echo StaticGuardAuthenticator::class.PHP_EOL; ?>
                    app.authenticator.query_string:
                        class: <?php echo QueryStringGuardAuthenticator::class.PHP_EOL; ?>
                        calls:
                            - [setUsernameField, ['_username']]
                            - [setPasswordField, ['_password']]
                <?php
            }

            protected function configureRoutes(RouteCollectionBuilder $routes)
            {
                $routes->add($path='/user/home', $controller='kernel:homeAction', $name='user_home');
                $routes->add($path='/user/list', $controller='kernel:homeAction', $name='user_list');

                $routes->add($path='/admin/home', $controller='kernel:homeAction', $name='admin_home');

                $routes->add($path='/anonymous/home', $controller='kernel:homeAction', $name='anonymous_home');

                $routes->add($path='/nonauthenticated/home', $controller='kernel:homeAction', $name='notauthenticated_home');

                $routes->add($path='/api/home', $controller='kernel:apiHomeAction', $name='api_home');
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

    public function testAccessAllowedWithFirewallAdmin()
    {
        $client = static::createClient();

        $client->request('GET', '/user/home');

        $this->assertEquals(
            'john',
            $client->getContainer()->get('security.token_storage')->getToken()->getUser()->getUsername()
        );
        $this->assertEquals('home', $client->getResponse()->getContent());
    }

    public function testAccessNotAllowedWithFirewallAdmin()
    {
        $client = static::createClient();

        $this->setExpectedException(AccessDeniedHttpException::class);

        $client->request('GET', '/admin/home');
    }

    public function testAuthenticated()
    {
        $client = static::createClient();

        $client->request('GET', '/user/list');

        $this->assertEquals(
            'john',
            $client->getContainer()->get('security.token_storage')->getToken()->getUser()->getUsername()
        );
        $this->assertEquals('home', $client->getResponse()->getContent());
    }

    public function testAnonymous()
    {
        $client = static::createClient();

        $client->request('GET', '/anonymous/home');

        $this->assertEquals(
            'anon.',
            $client->getContainer()->get('security.token_storage')->getToken()->getUser()
        );
        $this->assertEquals('home', $client->getResponse()->getContent());
    }

    public function testNotAuthenticated()
    {
        $client = static::createClient();

        $client->request('GET', '/nonauthenticated/home');

        $this->assertNull($client->getContainer()->get('security.token_storage')->getToken());
        $this->assertEquals('home', $client->getResponse()->getContent());
    }

    public function testSuccessAuthenticatedWithMobileFirewall()
    {
        $client = static::createClient();

        $client->request('GET', '/api/home?_username=john&_password=gates');

        $this->assertEquals(
            array('title' => 'home'),
            json_decode($client->getResponse()->getContent(), true),
            $client->getResponse()->getContent()
        );
    }

    public function testNotAuthenticatedWithMobileFirewall()
    {
        $client = static::createClient();

        $client->request('GET', '/api/home');

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }

    public function testAuthenticatedWithBadCredentialWithMobileFirewal()
    {
        $client = static::createClient();

        $client->request('GET', '/api/home?_username=admin&_password=admin');

        $this->assertEquals(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());
    }
}