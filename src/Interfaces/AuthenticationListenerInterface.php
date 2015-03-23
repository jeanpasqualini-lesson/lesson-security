<?php
/**
 * Created by PhpStorm.
 * User: darkilliant
 * Date: 3/14/15
 * Time: 1:37 AM.
 */

namespace Interfaces;

use Event\ConsoleEvent;

/**
 * Interface AuthenticationListenerInterface.
 */
interface AuthenticationListenerInterface
{
    /**
     * @param ConsoleEvent $event
     *
     * @return mixed
     */
    public function handle(ConsoleEvent $event);
}
