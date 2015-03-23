<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/14/15
 * Time: 2:27 AM.
 */

namespace Interfaces;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Interface AuthenticationEntryPoint.
 */
interface AuthenticationEntryPoint
{
    /**
     * @param OutputInterface         $output
     * @param AuthenticationException $authException
     *
     * @return mixed
     */
    public function start(OutputInterface $output, AuthenticationException $authException = null);
}
