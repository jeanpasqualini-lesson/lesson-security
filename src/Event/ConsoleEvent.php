<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/14/15
 * Time: 1:40 AM.
 */

namespace Event;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ConsoleEvent.
 */
class ConsoleEvent extends Event
{
    /**
     * @var entrée
     */
    private $input;

    /**
     * @var sortie
     */
    private $output;

    /**
     * evenement de gestion de la console.
     */
    const EVENT_CONSOLE = "onConsole";

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;

        $this->output = $output;
    }

    /**
     * @return entrée
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return sortie
     */
    public function getOutput()
    {
        return $this->output;
    }
}
