<?php

namespace tests\Provider;


use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FileProvider implements UserProviderInterface
{
    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function loadUserByUsername($username)
    {
        $data = str_replace(array("\r", "\n"), "", file_get_contents($this->filePath));

        list($storedUsername, $password) = explode(":", $data);

        if ($username === $storedUsername)
        {
            return new User($username, $password, array('ROLE_USER'));
        }

        throw new UsernameNotFoundException();
    }

    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }
}