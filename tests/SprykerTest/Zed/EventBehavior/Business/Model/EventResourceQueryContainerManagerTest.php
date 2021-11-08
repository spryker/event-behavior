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
        $eventResourceQueryContainerPlugin = $this->createEventResourceQueryContainerMockPlugin();

        $eventResourceQueryContainerPlugin->expects($this->once())
            ->method('queryData')
            ->will($this->returnValue(null));

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
            1,
        );
    }

    /**
     * @return \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createEventFacadeMockBridge(): EventBehaviorToEventInterface
    {
        return $this->getMockBuilder(EventBehaviorToEventInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
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
            ->setMethods([
                'queryData',
                'getResourceName',
                'getEventName',
                'getIdColumnName',
            ])->getMock();
    }
}
