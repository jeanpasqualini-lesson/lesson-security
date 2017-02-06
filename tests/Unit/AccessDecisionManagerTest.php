<?php
namespace tests\Fixture {

    use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
    use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

    class FakeVoter implements VoterInterface {

        protected $vote;

        public function __construct($vote)
        {
            $this->vote = $vote;
        }

        public function vote(TokenInterface $token, $subjet, array $attributes)
        {
            return $this->vote;
        }
    }
}

// Event listener : plus le nombre est grand, plus il est prioritaire
// Voter : plus de nombre est petit, plus il est prioritaires

namespace tests\Functionnal {

    use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
    use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
    use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
    use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
    use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
    use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
    use tests\Fixture\FakeVoter;

    /**
     * AcessDecisionManagerTest
     *
     * @author Jean Pasqualini <jpasqualini75@gmail.com>
     * @package tests\Functionnal;
     */
    class AccessDecisionManagerTest extends \PHPUnit_Framework_TestCase
    {
        public function createAuthorizationChecker(
            array $userRoles,
            array $votes,
            $strategy,
            $allowIfAllAbstain,
            $allowIfEqualGrantedDenied
        ) {
            $voters = array();
            foreach ($votes as $vote)
            {
                $voters[] = new FakeVoter($vote);
            }

            $token = new AnonymousToken(
                $secret = 'my_secret',
                $user = 'test',
                $roles = $userRoles
            );
            $tokenStorage = $this->createMock(TokenStorageInterface::class);
            $tokenStorage
                ->expects($this->any())
                ->method('getToken')
                ->will($this->returnValue($token));

            $authenticationManager = $this->createMock(AuthenticationManagerInterface::class);

            $accessDecisionManager = new AccessDecisionManager(
                $voters,
                $strategy,
                $allowIfAllAbstain,
                $allowIfEqualGrantedDenied
            );

            $authorizationChecker = new AuthorizationChecker(
                $tokenStorage,
                $authenticationManager,
                $accessDecisionManager,
                $alwaysAuthenticate = false
            );

            return $authorizationChecker;
        }

        public function testGrantedWithStrategyAffirmative()
        {
            /** @var AuthorizationChecker $authorizationChecker */
            $authorizationChecker = $this->createAuthorizationChecker(
                $userRoles                  = array('ROLE_USER'),
                $votes                      = array(
                    VoterInterface::ACCESS_DENIED,
                    VoterInterface::ACCESS_GRANTED,
                    VoterInterface::ACCESS_DENIED,
                ),
                $strategy                   = AccessDecisionManager::STRATEGY_AFFIRMATIVE,
                $allowIfAllAbstain          = false,
                $allowIfEqualGrantedDenied  = true
            );

            $this->assertTrue($authorizationChecker->isGranted('ROLE_USER'));
        }

        public function testGrantedWithStrategyUnanimous()
        {
            /** @var AuthorizationChecker $authorizationChecker */
            $authorizationChecker = $this->createAuthorizationChecker(
                $userRoles                  = array('ROLE_USER'),
                $votes                      = array(
                    VoterInterface::ACCESS_GRANTED,
                    VoterInterface::ACCESS_GRANTED,
                    VoterInterface::ACCESS_GRANTED,
                ),
                $strategy                   = AccessDecisionManager::STRATEGY_UNANIMOUS,
                $allowIfAllAbstain          = false,
                $allowIfEqualGrantedDenied  = true
            );

            $this->assertTrue($authorizationChecker->isGranted('ROLE_USER'));
        }

        public function testGrantedWithStrategyConsensus()
        {
            /** @var AuthorizationChecker $authorizationChecker */
            $authorizationChecker = $this->createAuthorizationChecker(
                $userRoles                  = array('ROLE_USER'),
                $votes                      = array(
                    VoterInterface::ACCESS_DENIED,
                    VoterInterface::ACCESS_GRANTED,
                    VoterInterface::ACCESS_DENIED,
                    VoterInterface::ACCESS_GRANTED,
                ),
                $strategy                   = AccessDecisionManager::STRATEGY_CONSENSUS,
                $allowIfAllAbstain          = false,
                $allowIfEqualGrantedDenied  = true
            );

            $this->assertTrue($authorizationChecker->isGranted('ROLE_USER'));

            /** @var AuthorizationChecker $authorizationChecker */
            $authorizationChecker = $this->createAuthorizationChecker(
                $userRoles                  = array('ROLE_USER'),
                $votes                      = array(
                    VoterInterface::ACCESS_DENIED,
                    VoterInterface::ACCESS_GRANTED,
                    VoterInterface::ACCESS_GRANTED,
                    VoterInterface::ACCESS_DENIED,
                    VoterInterface::ACCESS_GRANTED,
                ),
                $strategy                   = AccessDecisionManager::STRATEGY_CONSENSUS,
                $allowIfAllAbstain          = false,
                $allowIfEqualGrantedDenied  = true
            );

            $this->assertTrue($authorizationChecker->isGranted('ROLE_USER'));
        }

        public function testGrantedWithAllowIfAllAbstain()
        {
            /** @var AuthorizationChecker $authorizationChecker */
            $authorizationChecker = $this->createAuthorizationChecker(
                $userRoles                  = array('ROLE_USER'),
                $votes                      = array(
                    VoterInterface::ACCESS_ABSTAIN,
                    VoterInterface::ACCESS_ABSTAIN,
                    VoterInterface::ACCESS_ABSTAIN,
                ),
                $strategy                   = AccessDecisionManager::STRATEGY_AFFIRMATIVE,
                $allowIfAllAbstain          = true,
                $allowIfEqualGrantedDenied  = true
            );

            $this->assertTrue($authorizationChecker->isGranted('ROLE_USER'));

            /** @var AuthorizationChecker $authorizationChecker */
            $authorizationChecker = $this->createAuthorizationChecker(
                $userRoles                  = array('ROLE_USER'),
                $votes                      = array(
                    VoterInterface::ACCESS_ABSTAIN,
                    VoterInterface::ACCESS_ABSTAIN,
                    VoterInterface::ACCESS_ABSTAIN,
                ),
                $strategy                   = AccessDecisionManager::STRATEGY_UNANIMOUS,
                $allowIfAllAbstain          = true,
                $allowIfEqualGrantedDenied  = true
            );

            $this->assertTrue($authorizationChecker->isGranted('ROLE_USER'));

            /** @var AuthorizationChecker $authorizationChecker */
            $authorizationChecker = $this->createAuthorizationChecker(
                $userRoles                  = array('ROLE_USER'),
                $votes                      = array(
                    VoterInterface::ACCESS_ABSTAIN,
                    VoterInterface::ACCESS_ABSTAIN,
                    VoterInterface::ACCESS_ABSTAIN,
                ),
                $strategy                   = AccessDecisionManager::STRATEGY_CONSENSUS,
                $allowIfAllAbstain          = true,
                $allowIfEqualGrantedDenied  = true
            );

            $this->assertTrue($authorizationChecker->isGranted('ROLE_USER'));
        }

        public function testGrantedWithAllowIfEqualGrantedDenied()
        {
            /** @var AuthorizationChecker $authorizationChecker */
            $authorizationChecker = $this->createAuthorizationChecker(
                $userRoles                  = array('ROLE_USER'),
                $votes                      = array(
                    VoterInterface::ACCESS_DENIED,
                    VoterInterface::ACCESS_GRANTED,
                    VoterInterface::ACCESS_DENIED,
                    VoterInterface::ACCESS_GRANTED,
                ),
                $strategy                   = AccessDecisionManager::STRATEGY_CONSENSUS,
                $allowIfAllAbstain          = false,
                $allowIfEqualGrantedDenied  = true
            );

            $this->assertTrue($authorizationChecker->isGranted('ROLE_USER'));
        }

        public function testDeniedWithAllowIfEqualGrantedDenied()
        {
            /** @var AuthorizationChecker $authorizationChecker */
            $authorizationChecker = $this->createAuthorizationChecker(
                $userRoles                  = array('ROLE_USER'),
                $votes                      = array(
                    VoterInterface::ACCESS_DENIED,
                    VoterInterface::ACCESS_GRANTED,
                    VoterInterface::ACCESS_DENIED,
                    VoterInterface::ACCESS_GRANTED,
                ),
                $strategy                   = AccessDecisionManager::STRATEGY_CONSENSUS,
                $allowIfAllAbstain          = false,
                $allowIfEqualGrantedDenied  = false
            );

            $this->assertFalse($authorizationChecker->isGranted('ROLE_USER'));
        }
    }
}