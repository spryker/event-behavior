<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Glue\EventBehavior\Plugin\EventDispatcher;

use Codeception\Test\Unit;
use Spryker\Glue\EventBehavior\Plugin\EventDispatcher\EventBehaviorEventDispatcherPlugin;
use Spryker\Shared\EventDispatcher\EventDispatcher;
use Spryker\Shared\EventDispatcherExtension\Dependency\Plugin\EventDispatcherPluginInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Glue
 * @group EventBehavior
 * @group Plugin
 * @group EventDispatcher
 * @group EventBehaviorEventDispatcherPluginTest
 * Add your own group annotations below this line
 */
class EventBehaviorEventDispatcherPluginTest extends Unit
{
    /**
     * @var \SprykerTest\Glue\EventBehavior\EventBehaviorGlueTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testDispatchWillReturnTerminateEvent(): void
    {
        // Arrange
        $plugin = new EventBehaviorEventDispatcherPlugin();

        // Act
        $event = $this->dispatchEvent($plugin);

        // Assert
        $this->assertInstanceOf(TerminateEvent::class, $event);
    }

    /**
     * @param \Spryker\Shared\EventDispatcherExtension\Dependency\Plugin\EventDispatcherPluginInterface $plugin
     *
     * @return \Symfony\Component\HttpKernel\Event\TerminateEvent
     */
    protected function dispatchEvent(EventDispatcherPluginInterface $plugin): TerminateEvent
    {
        $eventDispatcher = new EventDispatcher();
        $plugin->extend($eventDispatcher, $this->tester->getContainer());

        /** @var \Symfony\Component\HttpKernel\Event\TerminateEvent $event */
        $event = $eventDispatcher->dispatch($this->tester->getTerminateEvent(), KernelEvents::TERMINATE);

        return $event;
    }
}
