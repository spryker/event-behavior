<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\EventBehavior\Business;

use Codeception\Test\Unit;
use DateInterval;
use DateTime;
use Generated\Shared\Transfer\EventEntityTransfer;
use Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChange;
use Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery;
use Spryker\Shared\Config\Config;
use Spryker\Shared\EventBehavior\EventBehaviorConstants;
use Spryker\Shared\Kernel\Transfer\TransferInterface;
use Spryker\Zed\AvailabilityStorage\Communication\Plugin\Event\AvailabilityEventResourcePlugin;
use Spryker\Zed\CategoryStorage\Communication\Plugin\Event\CategoryTreeEventResourcePlugin;
use Spryker\Zed\EventBehavior\Business\EventBehaviorBusinessFactory;
use Spryker\Zed\EventBehavior\Business\EventBehaviorFacade;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface;
use Spryker\Zed\EventBehavior\Dependency\Service\EventBehaviorToUtilEncodingInterface;
use Spryker\Zed\EventBehavior\EventBehaviorConfig;
use Spryker\Zed\EventBehavior\EventBehaviorDependencyProvider;
use Spryker\Zed\EventBehavior\Persistence\Propel\Behavior\EventBehavior;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Kernel\RequestIdentifier;

/**
 * Auto-generated group annotations
 * @group SprykerTest
 * @group Zed
 * @group EventBehavior
 * @group Business
 * @group Facade
 * @group EventBehaviorFacadeTest
 * Add your own group annotations below this line
 */
class EventBehaviorFacadeTest extends Unit
{
    const FOREIGN_KEYS = 'foreign_keys';
    const MODIFIED_COLUMNS = 'modified_columns';

    /**
     * @var \Spryker\Zed\EventBehavior\Business\EventBehaviorFacadeInterface
     */
    protected $eventBehaviorFacade;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->cleanupEventMemory();
        parent::setUp();
    }

    /**
     * @return void
     */
    public function testEventBehaviorWillTriggerMemoryEventsData()
    {
        $behaviorStatus = Config::get(EventBehaviorConstants::EVENT_BEHAVIOR_TRIGGERING_ACTIVE, false);
        if (!$behaviorStatus) {
            return;
        }

        $this->createEntityChangeEvent();

        $container = new Container();
        $container[EventBehaviorDependencyProvider::FACADE_EVENT] = function (Container $container) {
            $storageMock = $this->createEventFacadeMockBridge();
            $storageMock->expects($this->once())->method('trigger')->will(
                $this->returnCallback(
                    function ($eventName, TransferInterface $eventTransfer) {
                        $this->assertTriggeredEvent($eventName, $eventTransfer);
                    }
                )
            );

            return $storageMock;
        };

        $container = $this->generateUtilEncodingServiceMock($container);
        $this->prepareFacade($container);
        $this->eventBehaviorFacade->triggerRuntimeEvents();
    }

    /**
     * @return void
     */
    public function testEventBehaviorWillTriggerLostEventsData()
    {
        $behaviorStatus = Config::get(EventBehaviorConstants::EVENT_BEHAVIOR_TRIGGERING_ACTIVE, false);
        if (!$behaviorStatus) {
            return;
        }

        $this->createLostEntityChangeEvent();

        $container = new Container();
        $container[EventBehaviorDependencyProvider::FACADE_EVENT] = function (Container $container) {
            $storageMock = $this->createEventFacadeMockBridge();
            $storageMock->expects($this->once())->method('trigger')->will(
                $this->returnCallback(
                    function ($eventName, TransferInterface $eventTransfer) {
                        $this->assertTriggeredEvent($eventName, $eventTransfer);
                    }
                )
            );

            return $storageMock;
        };

        $container = $this->generateUtilEncodingServiceMock($container);
        $this->prepareFacade($container);
        $this->eventBehaviorFacade->triggerLostEvents();
    }

    /**
     * @return void
     */
    public function testExecuteResolvedPluginsBySources(): void
    {
        $behaviorStatus = Config::get(EventBehaviorConstants::EVENT_BEHAVIOR_TRIGGERING_ACTIVE, false);
        if (!$behaviorStatus) {
            return;
        }

        $this->createEntityChangeEvent();

        $container = $this->prepareContainerForExecuteResolvedPluginsBySourcesTest();
        $this->prepareFacade($container);
        $this->eventBehaviorFacade->executeResolvedPluginsBySources([],[]);
    }

    /**
     * @return void
     */
    public function testGetEventTransferIds(): void
    {
        $container = new Container();
        $this->prepareFacade($container);

        $eventEntityTransfers = [];

        $eventEntityTransfer = new EventEntityTransfer();
        $eventEntityTransfer->setId(1);
        $eventEntityTransfers[] = $eventEntityTransfer;

        $eventEntityTransfer = new EventEntityTransfer();
        $eventEntityTransfer->setId(2);
        $eventEntityTransfers[] = $eventEntityTransfer;

        $eventEntityTransfer = new EventEntityTransfer();
        $eventEntityTransfer->setId(1);
        $eventEntityTransfers[] = $eventEntityTransfer;

        $eventTransferIds = $this->eventBehaviorFacade->getEventTransferIds($eventEntityTransfers);
        $this->assertEquals($eventTransferIds, [1,2]);
    }

    /**
     * @return void
     */
    public function testGetEventTransferForeignKeys(): void
    {
        $container = new Container();
        $this->prepareFacade($container);

        $eventEntityTransfers = [];

        $eventEntityTransfer = new EventEntityTransfer();
        $eventEntityTransfer->setForeignKeys(['testForeignKey' => 'keyValue1']);
        $eventEntityTransfers[] = $eventEntityTransfer;

        $eventEntityTransfer = new EventEntityTransfer();
        $eventEntityTransfer->setForeignKeys(['testForeignKey' => 'keyValue2']);
        $eventEntityTransfers[] = $eventEntityTransfer;

        $eventEntityTransfer = new EventEntityTransfer();
        $eventEntityTransfer->setForeignKeys(['testForeignKey' => 'keyValue1']);
        $eventEntityTransfers[] = $eventEntityTransfer;

        $eventTransferForeignKeys = $this->eventBehaviorFacade->getEventTransferForeignKeys($eventEntityTransfers, 'testForeignKey');
        $this->assertEquals($eventTransferForeignKeys, ['keyValue1','keyValue2']);
    }

    /**
     * @return void
     */
    public function testGetEventTransfersByModifiedColumns(): void
    {
        $container = new Container();
        $this->prepareFacade($container);

        $eventEntityTransfers = [];

        $modifiedColumns = ['column1', 'column2', 'column3'];
        $eventEntityModifiedTransfer = new EventEntityTransfer();
        $eventEntityModifiedTransfer->setModifiedColumns($modifiedColumns);
        $eventEntityTransfers[] = $eventEntityModifiedTransfer;

        $notModifiedColumns = ['testColumn1','testColumn2','testColumn3'];
        $eventEntityTransfer = new EventEntityTransfer();
        $eventEntityTransfer->setModifiedColumns($notModifiedColumns);
        $eventEntityTransfers[] = $eventEntityTransfer;

        $eventTransfersWithModifiedColumns = $this->eventBehaviorFacade->getEventTransfersByModifiedColumns($eventEntityTransfers, $modifiedColumns);
        $this->assertEquals($eventTransfersWithModifiedColumns, [$eventEntityModifiedTransfer]);
    }

    /**
     * @param string $eventName
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $eventTransfer
     *
     * @return void
     */
    public function assertTriggeredEvent($eventName, TransferInterface $eventTransfer)
    {
        $this->assertEquals($eventName, 'test');
        $actualArray = $eventTransfer->toArray();

        $actualArray[EventBehavior::EVENT_CHANGE_ENTITY_FOREIGN_KEYS] = $actualArray[self::FOREIGN_KEYS];
        unset($actualArray[self::FOREIGN_KEYS]);

        $actualArray[EventBehavior::EVENT_CHANGE_ENTITY_MODIFIED_COLUMNS] = $actualArray[self::MODIFIED_COLUMNS];
        unset($actualArray[self::MODIFIED_COLUMNS]);

        $this->assertEquals($actualArray, $this->createEventData());
    }

    /**
     * @param string $eventName
     *
     * @return void
     */
    protected function assertTriggeredResourceEvent(string $eventName): void
    {
        $resources = [];
        foreach ($this->getEventTriggerResourcePlugins() as $resourcePlugin) {
            $resources[] = $resourcePlugin->getEventName();
        }

        $this->assertContains($eventName, $resources);
    }

    /**
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function prepareContainerForExecuteResolvedPluginsBySourcesTest(): Container
    {
        $container = new Container();
        $container[EventBehaviorDependencyProvider::FACADE_EVENT] = function () {
            $storageMock = $this->createEventFacadeMockBridge();
            if (count($this->getEventTriggerResourcePlugins())) {
                $storageMock->expects($this->any())->method('trigger')->will(
                    $this->returnCallback(
                        function ($eventName) {
                            $this->assertTriggeredResourceEvent($eventName);
                        }
                    )
                );

                return $storageMock;
            }
            $storageMock->expects($this->never())->method('trigger');

            return $storageMock;
        };

        $container[EventBehaviorDependencyProvider::PLUGINS_EVENT_TRIGGER_RESOURCE] = function () {
            return $this->getEventTriggerResourcePlugins();
        };

        $container = $this->generateUtilEncodingServiceMock($container);

        return $container;
    }

    /**
     * @return \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[]
     */
    protected function getEventTriggerResourcePlugins(): array
    {
        return [
            new AvailabilityEventResourcePlugin(),
            new CategoryTreeEventResourcePlugin(),
        ];
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createEventFacadeMockBridge()
    {
        return $this->getMockBuilder(EventBehaviorToEventInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'trigger',
            ])
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createUtilEncodingServiceBridge()
    {
        return $this->getMockBuilder(EventBehaviorToUtilEncodingInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'encodeJson',
                'decodeJson',
            ])
            ->getMock();
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return void
     */
    protected function prepareFacade(Container $container)
    {
        $eventBehaviorBusinessFactory = new EventBehaviorBusinessFactory();
        $eventBehaviorBusinessFactory->setContainer($container);

        $this->eventBehaviorFacade = new EventBehaviorFacade();
        $this->eventBehaviorFacade->setFactory($eventBehaviorBusinessFactory);
    }

    /**
     * @return void
     */
    protected function createEntityChangeEvent()
    {
        $spyEventEntityChange = new SpyEventBehaviorEntityChange();
        $spyEventEntityChange->setProcessId(RequestIdentifier::getRequestId());
        $spyEventEntityChange->setData(json_encode($this->createEventData()));
        $spyEventEntityChange->save();
    }

    /**
     * @return void
     */
    protected function createLostEntityChangeEvent()
    {
        $spyEventEntityChange = new SpyEventBehaviorEntityChange();
        $spyEventEntityChange->setProcessId(RequestIdentifier::getRequestId());
        $spyEventEntityChange->setData(json_encode($this->createEventData()));
        $defaultTimeout = sprintf('PT%dM', EventBehaviorConfig::EVENT_ENTITY_CHANGE_TIMEOUT_MINUTE + 1);
        $date = new DateTime();
        $date->sub(new DateInterval($defaultTimeout));
        $spyEventEntityChange->setCreatedAt($date);
        $spyEventEntityChange->save();
    }

    /**
     * @return array
     */
    protected function createEventData()
    {
        return [
            EventBehavior::EVENT_CHANGE_ENTITY_NAME => 'name',
            EventBehavior::EVENT_CHANGE_ENTITY_ID => '123',
            EventBehavior::EVENT_CHANGE_ENTITY_FOREIGN_KEYS => [1, 2, 3],
            EventBehavior::EVENT_CHANGE_NAME => 'test',
            EventBehavior::EVENT_CHANGE_ENTITY_MODIFIED_COLUMNS => [],

        ];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function generateUtilEncodingServiceMock(Container $container)
    {
        $container[EventBehaviorDependencyProvider::SERVICE_UTIL_ENCODING] = function (Container $container) {
            $utilEncodingMock = $this->createUtilEncodingServiceBridge();
            $utilEncodingMock->expects($this->once())
                ->method('decodeJson')
                ->will($this->returnCallback(function ($data) {
                    return json_decode($data, true);
                }));
            return $utilEncodingMock;
        };

        return $container;
    }

    /**
     * @return void
     */
    protected function cleanupEventMemory()
    {
        SpyEventBehaviorEntityChangeQuery::create()->deleteAll();
    }
}
