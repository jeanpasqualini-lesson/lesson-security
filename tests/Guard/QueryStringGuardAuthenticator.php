<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace tests\Guard;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;


/**
 * QueryStringGuardAuthenticator
 *
 * @author Jean Pasqualini <jpasqualini75@gmail.com>
 * @package tests\Guard;
 */
class QueryStringGuardAuthenticator extends AbstractGuardAuthenticator
{
    protected $usernameField;
    protected $passwordField;
    protected $activeRememberMe;

    public function setUsernameField($fieldName)
    {
        $this->usernameField = $fieldName;
    }

    public function setPasswordField($fieldName)
    {
        $this->passwordField = $fieldName;
    }

    public function activeRememberMe()
    {
        $this->activeRememberMe = true;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new Response('not authenticated', Response::HTTP_UNAUTHORIZED);
    }

    public function getCredentials(Request $request)
    {
        $credidential = array(
            'username' => $request->query->get($this->usernameField, null),
            'password' => $request->query->get($this->passwordField, null),
        );

        return (null !== $credidential['username'] || null !== $credidential['password'])
            ? $credidential
            : null;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials['username']);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $user->getPassword() === $credentials['password'];
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response('not authorized', Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return ($this->activeRememberMe) ? new Response() : null;
    }

    public function supportsRememberMe()
    {
        return $this->activeRememberMe;
    }

}