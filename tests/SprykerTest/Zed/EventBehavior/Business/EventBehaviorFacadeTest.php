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
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;
use Spryker\Shared\Config\Config;
use Spryker\Shared\EventBehavior\EventBehaviorConstants;
use Spryker\Shared\Kernel\Transfer\TransferInterface;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface;
use Spryker\Zed\EventBehavior\Dependency\Service\EventBehaviorToUtilEncodingInterface;
use Spryker\Zed\EventBehavior\EventBehaviorConfig;
use Spryker\Zed\EventBehavior\EventBehaviorDependencyProvider;
use Spryker\Zed\EventBehavior\Persistence\Propel\Behavior\EventBehavior;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\Kernel\RequestIdentifier;
use SprykerTest\Zed\EventBehavior\EventBehaviorBusinessTester;

/**
 * Auto-generated group annotations
 *
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
    /**
     * @var string
     */
    protected const ID = 'id';

    /**
     * @var string
     */
    protected const FOREIGN_KEYS = 'foreign_keys';

    /**
     * @var string
     */
    protected const MODIFIED_COLUMNS = 'modified_columns';

    /**
     * @var string
     */
    protected const ORIGINAL_VALUES = 'original_values';

    /**
     * @var string
     */
    protected const FIELD_ADDITIONAL_VALUES = 'additional_values';

    /**
     * @var \Spryker\Zed\EventBehavior\Business\EventBehaviorFacadeInterface
     */
    protected $eventBehaviorFacade;

    /**
     * @var \SprykerTest\Zed\EventBehavior\EventBehaviorBusinessTester
     */
    protected EventBehaviorBusinessTester $tester;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->cleanupEventMemory();
        parent::setUp();
    }

    /**
     * @return void
     */
    public function testEventBehaviorWillTriggerMemoryEventDataForTheSameEntities(): void
    {
        $eventFacadeMock = $this->createEventFacadeMockBridge();
        $eventFacadeMock->expects($this->once())->method('triggerBulk')->will(
            $this->returnCallback(
                function ($eventName, array $eventTransfers): void {
                    $this->assertCount(1, $eventTransfers);
                    foreach ($eventTransfers as $eventTransfer) {
                        $this->assertTriggeredEvent($eventName, $eventTransfer);
                    }
                },
            ),
        );
        $this->tester->setDependency(EventBehaviorDependencyProvider::FACADE_EVENT, $eventFacadeMock);
        $this->setDependencyUtilEncodingService($this->exactly(2));
        $this->createEntityChangeEvent();
        $this->createEntityChangeEvent();

        $this->tester->getFacade()->triggerRuntimeEvents();
    }

    /**
     * @return void
     */
    public function testEventBehaviorWillTriggerMemoryEventsData(): void
    {
        $eventFacadeMock = $this->createEventFacadeMockBridge();
        $eventFacadeMock->expects($this->once())->method('triggerBulk')->will(
            $this->returnCallback(
                function ($eventName, array $eventTransfers): void {
                    $this->assertCount(2, $eventTransfers);
                    foreach ($eventTransfers as $eventTransfer) {
                        $this->assertTriggeredEvent($eventName, $eventTransfer);
                    }
                },
            ),
        );
        $this->tester->setDependency(EventBehaviorDependencyProvider::FACADE_EVENT, $eventFacadeMock);
        $this->setDependencyUtilEncodingService($this->exactly(2));
        $this->createEntityChangeEvent('321');
        $this->createEntityChangeEvent();

        $this->tester->getFacade()->triggerRuntimeEvents();
    }

    /**
     * @return void
     */
    public function testEventBehaviorWillTriggerLostEventsData(): void
    {
        $this->createLostEntityChangeEvent();

        $storageMock = $this->createEventFacadeMockBridge();
        $storageMock->expects($this->once())->method('triggerBulk')->will(
            $this->returnCallback(
                function ($eventName, array $eventTransfers): void {
                    foreach ($eventTransfers as $eventTransfer) {
                        $this->assertTriggeredEvent($eventName, $eventTransfer);
                    }
                },
            ),
        );
        $this->tester->setDependency(EventBehaviorDependencyProvider::FACADE_EVENT, $storageMock);
        $this->setDependencyUtilEncodingService($this->once());

        $this->tester->getFacade()->triggerLostEvents();
    }

    /**
     * @return void
     */
    public function testExecuteResolvedPluginsBySources(): void
    {
        $this->createEntityChangeEvent();

        $storageMock = $this->createEventFacadeMockBridge();
        if (count($this->getEventTriggerResourcePlugins())) {
            $storageMock->expects($this->any())->method('trigger')->will(
                $this->returnCallback(
                    function ($eventName): void {
                        $this->assertTriggeredResourceEvent($eventName);
                    },
                ),
            );
        } else {
            $storageMock->expects($this->never())->method('trigger');
        }

        $this->tester->setDependency(EventBehaviorDependencyProvider::FACADE_EVENT, $storageMock);
        $this->tester->setDependency(EventBehaviorDependencyProvider::PLUGINS_EVENT_TRIGGER_RESOURCE, $this->getEventTriggerResourcePlugins());

        $this->tester->getFacade()->executeResolvedPluginsBySources([], []);
    }

    /**
     * @return void
     */
    public function testGetEventTransferIds(): void
    {
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

        $eventTransferIds = $this->tester->getFacade()->getEventTransferIds($eventEntityTransfers);
        $this->assertEquals($eventTransferIds, [1, 2]);
    }

    /**
     * @return void
     */
    public function testGetGroupedEventTransferForeignKeysByForeignKey(): void
    {
        $container = new Container();
        $selectedForeignKey = 'foreign_key1';
        $expectedGroupedEventTransferForeignKeys = [];
        $eventEntityTransfers = [];

        $eventEntityTransfer = new EventEntityTransfer();
        $foreignKeys = [
            'foreign_key1' => 'value1',
            'foreign_key2' => 'value2',
            'foreign_key3' => 'value3',
        ];
        $eventEntityTransfer->setForeignKeys($foreignKeys);
        $eventEntityTransfers[] = $eventEntityTransfer;
        $expectedGroupedEventTransferForeignKeys[$foreignKeys[$selectedForeignKey]][] = $foreignKeys;

        $eventEntityTransfer = new EventEntityTransfer();
        $foreignKeys = [
            'foreign_key1' => 'value3',
            'foreign_key2' => 'value2',
            'foreign_key3' => 'value1',
        ];
        $eventEntityTransfer->setForeignKeys($foreignKeys);
        $eventEntityTransfers[] = $eventEntityTransfer;
        $expectedGroupedEventTransferForeignKeys[$foreignKeys[$selectedForeignKey]][] = $foreignKeys;

        $eventEntityTransfer = new EventEntityTransfer();
        $foreignKeys = [
            'foreign_key2' => 'value2',
            'foreign_key3' => 'value1',
        ];
        $eventEntityTransfer->setForeignKeys($foreignKeys);
        $eventEntityTransfers[] = $eventEntityTransfer;

        $eventEntityTransfer = new EventEntityTransfer();
        $foreignKeys = [
            'foreign_key1' => 'value1',
            'foreign_key4' => 'value4',
            'foreign_key5' => 'value5',
        ];
        $eventEntityTransfer->setForeignKeys($foreignKeys);
        $eventEntityTransfers[] = $eventEntityTransfer;
        $expectedGroupedEventTransferForeignKeys[$foreignKeys[$selectedForeignKey]][] = $foreignKeys;

        $groupedEventTransferForeignKeys = $this->tester->getFacade()->getGroupedEventTransferForeignKeysByForeignKey($eventEntityTransfers, $selectedForeignKey);

        $this->assertEquals(2, count($groupedEventTransferForeignKeys));
        $this->assertEquals($expectedGroupedEventTransferForeignKeys, $groupedEventTransferForeignKeys);
    }

    /**
     * @return void
     */
    public function testGetEventTransferForeignKeys(): void
    {
        $container = new Container();

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

        $eventTransferForeignKeys = $this->tester->getFacade()->getEventTransferForeignKeys($eventEntityTransfers, 'testForeignKey');
        $this->assertEquals($eventTransferForeignKeys, ['keyValue1', 'keyValue2']);
    }

    /**
     * @return void
     */
    public function testGetEventTransfersByModifiedColumns(): void
    {
        $eventEntityTransfers = [];

        $modifiedColumns = ['column1', 'column2', 'column3'];
        $eventEntityModifiedTransfer = new EventEntityTransfer();
        $eventEntityModifiedTransfer->setModifiedColumns($modifiedColumns);
        $eventEntityTransfers[] = $eventEntityModifiedTransfer;

        $notModifiedColumns = ['testColumn1', 'testColumn2', 'testColumn3'];
        $eventEntityTransfer = new EventEntityTransfer();
        $eventEntityTransfer->setModifiedColumns($notModifiedColumns);
        $eventEntityTransfers[] = $eventEntityTransfer;

        $eventTransfersWithModifiedColumns = $this->tester->getFacade()->getEventTransfersByModifiedColumns($eventEntityTransfers, $modifiedColumns);
        $this->assertEquals($eventTransfersWithModifiedColumns, [$eventEntityModifiedTransfer]);
    }

    /**
     * @param string $eventName
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $eventTransfer
     *
     * @return void
     */
    public function assertTriggeredEvent(string $eventName, TransferInterface $eventTransfer): void
    {
        $this->assertEquals($eventName, 'test');
        $actualArray = $eventTransfer->toArray();

        $actualArray[EventBehavior::EVENT_CHANGE_ENTITY_FOREIGN_KEYS] = $actualArray[static::FOREIGN_KEYS];
        unset($actualArray[static::FOREIGN_KEYS]);

        $actualArray[EventBehavior::EVENT_CHANGE_ENTITY_MODIFIED_COLUMNS] = $actualArray[static::MODIFIED_COLUMNS];
        unset($actualArray[static::MODIFIED_COLUMNS]);

        $actualArray[EventBehavior::EVENT_CHANGE_ENTITY_ORIGINAL_VALUES] = $actualArray[static::ORIGINAL_VALUES];
        unset($actualArray[static::ORIGINAL_VALUES]);

        $actualArray[EventBehavior::EVENT_CHANGE_ENTITY_ADDITIONAL_VALUES] = $actualArray[static::FIELD_ADDITIONAL_VALUES];
        unset($actualArray[static::FIELD_ADDITIONAL_VALUES]);

        $this->assertEquals($actualArray, $this->createEventData($actualArray[static::ID]));
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
     * @return array<\Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface>
     */
    protected function getEventTriggerResourcePlugins(): array
    {
        return [];
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
     * @return \Spryker\Zed\EventBehavior\Dependency\Service\EventBehaviorToUtilEncodingInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createUtilEncodingServiceBridge(): EventBehaviorToUtilEncodingInterface
    {
        return $this->getMockBuilder(EventBehaviorToUtilEncodingInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'encodeJson',
                'decodeJson',
                'decodeFromFormat',
            ])
            ->getMock();
    }

    /**
     * @param string $idEntity
     *
     * @return void
     */
    protected function createEntityChangeEvent(string $idEntity = '123'): void
    {
        $spyEventEntityChange = new SpyEventBehaviorEntityChange();
        $spyEventEntityChange->setProcessId(RequestIdentifier::getRequestId());
        $spyEventEntityChange->setData(json_encode($this->createEventData($idEntity)));
        $spyEventEntityChange->save();
    }

    /**
     * @param string $idEntity
     *
     * @return void
     */
    protected function createLostEntityChangeEvent(string $idEntity = '123'): void
    {
        $spyEventEntityChange = new SpyEventBehaviorEntityChange();
        $spyEventEntityChange->setProcessId(RequestIdentifier::getRequestId());
        $spyEventEntityChange->setData(json_encode($this->createEventData($idEntity)));
        $defaultTimeout = sprintf('PT%dM', EventBehaviorConfig::EVENT_ENTITY_CHANGE_TIMEOUT_MINUTE + 1);
        $date = new DateTime();
        $date->sub(new DateInterval($defaultTimeout));
        $spyEventEntityChange->setCreatedAt($date);
        $spyEventEntityChange->save();
    }

    /**
     * @param string $idEntity
     *
     * @return array
     */
    protected function createEventData(string $idEntity = '123'): array
    {
        return [
            EventBehavior::EVENT_CHANGE_ENTITY_NAME => 'name',
            EventBehavior::EVENT_CHANGE_ENTITY_ID => $idEntity,
            EventBehavior::EVENT_CHANGE_ENTITY_FOREIGN_KEYS => [1, 2, 3],
            EventBehavior::EVENT_CHANGE_NAME => 'test',
            EventBehavior::EVENT_CHANGE_ENTITY_MODIFIED_COLUMNS => [],
            EventBehavior::EVENT_CHANGE_ENTITY_ORIGINAL_VALUES => [],
            EventBehavior::EVENT_CHANGE_ENTITY_ADDITIONAL_VALUES => [],
        ];
    }

    /**
     * @param \PHPUnit\Framework\MockObject\Rule\InvokedCount $invokedCount
     *
     * @return void
     */
    protected function setDependencyUtilEncodingService(InvokedCountMatcher $invokedCount)
    {
        $utilEncodingMock = $this->createUtilEncodingServiceBridge();
        $utilEncodingMock->expects($invokedCount)
            ->method('decodeJson')
            ->will($this->returnCallback(function ($data) {
                return json_decode($data, true);
            }));

        $this->tester->setDependency(EventBehaviorDependencyProvider::SERVICE_UTIL_ENCODING, $utilEncodingMock);
    }

    /**
     * @return void
     */
    protected function cleanupEventMemory(): void
    {
        SpyEventBehaviorEntityChangeQuery::create()->deleteAll();
    }
}
