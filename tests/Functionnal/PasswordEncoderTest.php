<?php
namespace UserFixture {

    use Symfony\Component\Security\Core\User\UserInterface;

    abstract class UserAlgorythNotSupported implements UserInterface {}
    abstract class UserMessageDigestMd5 implements UserInterface {}
    abstract class UserSha512Alternative1 implements UserInterface {}
    abstract class UserSha512Alternative2 implements UserInterface {}
    abstract class UserCustomEncoder implements UserInterface {}
    abstract class UserPlainText implements UserInterface {}
    abstract class UserPlainTextWithSalt implements UserInterface {}
    abstract class UserBcrypt implements UserInterface {}
    abstract class UserPbkdf2 implements UserInterface {}
}

namespace Encoder {

    use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

    class ReverseEncoder implements PasswordEncoderInterface {
        public function encodePassword($raw, $salt)
        {
            return strrev($raw.$salt);
        }

        public function isPasswordValid($encoded, $raw, $salt)
        {
            return $encoded === $this->encodePassword($raw, $salt);
        }
    }
}

namespace tests\Functionnal {

    // algorithm
    // ------------
    // plaintext    = Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder
    // pbkdf2       = Symfony\Component\Security\Core\Encoder\Pbkdf2PasswordEncoder
    // bcrypt       = Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder
    // other        = Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder

    use Encoder\ReverseEncoder;
    use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
    use Symfony\Component\Routing\RouteCollectionBuilder;
    use tests\TestKernel;
    use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
    use UserFixture\UserAlgorythNotSupported;
    use UserFixture\UserBcrypt;
    use UserFixture\UserCustomEncoder;
    use UserFixture\UserMessageDigestMd5;
    use UserFixture\UserPbkdf2;
    use UserFixture\UserPlainText;
    use UserFixture\UserPlainTextWithSalt;
    use UserFixture\UserSha512Alternative1;
    use UserFixture\UserSha512Alternative2;

    class PasswordEncoderTest extends WebTestCase
    {
        protected static function createKernel(array $options = array())
        {
            $environment = isset($options['environment']) ? $options['environment'] : 'passwordencoder';

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
                            <?php echo UserAlgorythNotSupported::class; ?>:
                                algorithm: qdqsd

                            <?php echo UserMessageDigestMd5::class ?>: md5

                            <?php echo UserSha512Alternative1::class ?>: sha512
                            <?php echo UserSha512Alternative2::class ?>:
                                algorithm: sha512
                                encode_as_base64: true
                                iterations: 5000

                            <?php echo UserCustomEncoder::class ?>:
                                id: app.encoder.reverse

                            <?php echo UserPlainText::class ?>:
                                algorithm: plaintext
                                ignore_case: false
                            <?php echo UserPlainTextWithSalt::class ?>:
                                algorithm: plaintext
                                ignore_case: false

                            <?php echo UserBcrypt::class ?>:
                                algorithm: bcrypt
                                cost: 13

                            <?php echo UserPbkdf2::class ?>:
                                algorithm: pbkdf2
                                hash_algorithm: sha512
                                encode_as_base64: true
                                iterations: 1000
                                key_length: 40

                        providers:
                            main:
                                memory: true
                        firewalls:
                            main:
                                security: false
                        access_control: []
                    services:
                        app.encoder.reverse:
                            class: <?php echo ReverseEncoder::class.PHP_EOL; ?>
                    <?php
                }

                final protected function configureRoutes(RouteCollectionBuilder $routes) { }
            };
        }

        protected function encode($class, $password, $salt = null)
        {
            $kernel = static::createKernel();
            $kernel->boot();

            /** @var UserPasswordEncoder $encoder */
            $encoder = $kernel->getContainer()->get('security.password_encoder');

            $user = $this->getMockBuilder($class)
                ->disableOriginalConstructor()
                ->getMock();

            $user
                ->expects($this->any())
                ->method('getSalt')
                ->will($this->returnValue($salt));

            return $encoder->encodePassword($user, $password);
        }

        protected function isPasswordValid($class, $encodedPassword, $rawPassword)
        {
            $kernel = static::createKernel();
            $kernel->boot();

            /** @var UserPasswordEncoder $encoder */
            $encoder = $kernel->getContainer()->get('security.password_encoder');

            $user = $this->getMockBuilder($class)
                ->disableOriginalConstructor()
                ->getMock();

            $user
                ->expects($this->any())
                ->method('getPassword')
                ->will($this->returnValue($encodedPassword))
            ;

            return $encoder->isPasswordValid($user, $rawPassword);
        }

        public function testAlgorythmNotSupported()
        {
            $this->setExpectedException(\LogicException::class);

            $this->encode(UserAlgorythNotSupported::class, 'admin');
        }

        public function testMessageDigestMd5()
        {
            $passwordEncoded = $this->encode(UserMessageDigestMd5::class, 'admin');

            // 1. Concatene password and salt
            // 2. hash($algo='md5', $password, $rawBinary=true) in 5000 itération
            // 3. Encode in base64

            $this->assertEquals('w1GnuttsfgppBy/OeKCSUA==', $passwordEncoded);
        }

        public function testSha512()
        {
            $passwordEncoded1 = $this->encode(UserSha512Alternative1::class, 'admin');
            $passwordEncoded2 = $this->encode(UserSha512Alternative2::class, 'admin');

            $this->assertEquals($passwordEncoded1, $passwordEncoded2);
        }

        public function testCustomEncoder()
        {
            $passwordEncoded = $this->encode(UserCustomEncoder::class, 'admin');

            $this->assertEquals('nimda', $passwordEncoded);
        }

        public function testBcrypt()
        {
            $passwordEncoded = $this->encode(UserBcrypt::class, 'admin');

            $this->assertTrue($this->isPasswordValid(UserBcrypt::class, $passwordEncoded, 'admin'));
        }

        public function testPlainText()
        {
            $passwordEncoded = $this->encode(UserPlainText::class, 'admin');

            $this->assertEquals('admin', $passwordEncoded);
        }

        public function testPlainTextWithSalt()
        {
            $passwordEncoded = $this->encode(UserPlainTextWithSalt::class, $password = 'admin', $salt = 'pokemon');

            $this->assertEquals('admin{pokemon}', $passwordEncoded);
        }

        public function testPbkdf2()
        {
            $passwordEncoded = $this->encode(UserPbkdf2::class, $password = 'admin');

            $this->assertEquals('vEoWC4+1FCXNFQUvvKy752acAyAAkuQnEeG+Og5ybXegIDrD97n7fw==', $passwordEncoded);
        }
    }
}