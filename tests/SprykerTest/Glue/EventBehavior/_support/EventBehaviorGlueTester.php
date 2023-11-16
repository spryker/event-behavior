<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerTest\Glue\EventBehavior;

use Codeception\Actor;
use Codeception\Stub;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Inherited Methods
 *
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(\SprykerTest\Glue\EventBehavior\PHPMD)
 */
class EventBehaviorGlueTester extends Actor
{
    use _generated\EventBehaviorGlueTesterActions;

    /**
     * @return \Symfony\Component\HttpKernel\Event\TerminateEvent
     */
    public function getTerminateEvent(): TerminateEvent
    {
        return new TerminateEvent($this->getHttpKernelMock(), Request::createFromGlobals(), new Response());
    }

    /**
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    protected function getHttpKernelMock(): HttpKernelInterface
    {
        /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernelMock */
        $httpKernelMock = Stub::makeEmpty(HttpKernelInterface::class);

        return $httpKernelMock;
    }
}
