<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/14/15
 * Time: 2:27 AM.
 */

namespace EntryPoint;

use Interfaces\AuthenticationEntryPointInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class ConsoleEntryPoint.
 */
class ConsoleEntryPoint /**implements AuthenticationEntryPointInterface*/
{
    /**
     * @param OutputInterface         $output
     * @param AuthenticationException $authException
     *
     * @return mixed
     */
    public function start(OutputInterface $output, AuthenticationException $authException = null)
    {
        // TODO: Implement start() method.

        $output->writeln("required logged");

        $questionHelper = new QuestionHelper();

        $username = $questionHelper->ask(new ArgvInput(), $output, new Question("username : ", "unknow"));

        $password = $questionHelper->ask(new ArgvInput(), $output, new Question("password : ", "unknow"));

        return [
            "username" => $username,
            "password" => $password,
        ];
    }
}
