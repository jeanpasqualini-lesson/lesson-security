<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/14/15
 * Time: 1:04 AM.
 */

namespace Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Class LoginPasswordToken.
 */
class LoginPasswordToken extends AbstractToken
{
    private $password;

    /**
     * @param string $login
     * @param string $password
     * @param array  $roles
     */
    public function __construct($login, $password, array $roles = array())
    {
        parent::__construct($roles);
        parent::setAuthenticated(count($roles) > 0);
        $this->setUser((string) $login);
        $this->password = $password;
    }

    /**
     *
     */
    public function getCredentials()
    {
        return $this->password;
    }
}
