<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\EventBehavior\Business\Model;

use Codeception\Test\Unit;
use Spryker\Zed\EventBehavior\Business\Model\EventResourceQueryContainerManager;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface;
use Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface;
use Spryker\Zed\EventBehavior\EventBehaviorConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group EventBehavior
 * @group Business
 * @group Model
 * @group EventResourceQueryContainerManagerTest
 * Add your own group annotations below this line
 */
class EventResourceQueryContainerManagerTest extends Unit
{
    /**
     * @return void
     */
    public function testTriggerResourceEventsWithNoQuery(): void
    {
        // Arrange
        $eventResourceQueryContainerPlugin = $this->createEventResourceQueryContainerMockPlugin();

        // Assert
        $eventResourceQueryContainerPlugin->expects($this->once())
            ->method('queryData')
            ->willReturn(null);

        // Act
        $this->createEventResourceQueryContainerManager()
            ->processResourceEvents([
                $eventResourceQueryContainerPlugin,
            ]);
    }

    /**
     * @return \Spryker\Zed\EventBehavior\Business\Model\EventResourceQueryContainerManager
     */
    protected function createEventResourceQueryContainerManager(): EventResourceQueryContainerManager
    {
        return new EventResourceQueryContainerManager(
            $this->createEventFacadeMockBridge(),
            new EventBehaviorConfig(),
        );
    }

    /**
     * @return \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createEventFacadeMockBridge(): EventBehaviorToEventInterface
    {
        return $this->getMockBuilder(EventBehaviorToEventInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'trigger',
                'triggerBulk',
                'triggerByListenerName',
            ])
            ->getMock();
    }

    /**
     * @return \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceQueryContainerPluginInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createEventResourceQueryContainerMockPlugin(): EventResourceQueryContainerPluginInterface
    {
        return $this->getMockBuilder(EventResourceQueryContainerPluginInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'queryData',
                'getResourceName',
                'getEventName',
                'getIdColumnName',
            ])->getMock();
    }
}
