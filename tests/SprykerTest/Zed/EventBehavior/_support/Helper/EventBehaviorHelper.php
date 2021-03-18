<?php

/**
 * This file is part of the Spryker Suite.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerTest\Zed\EventBehavior\Helper;

use Codeception\Stub;
use Orm\Zed\EventBehavior\Persistence\Map\SpyEventBehaviorEntityChangeTableMap;
use Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery;
use Propel\Runtime\Collection\ObjectCollection;
use Spryker\Client\Queue\Model\Proxy\QueueProxy;
use Spryker\Zed\Event\Dependency\Client\EventToQueueBridge;
use Spryker\Zed\EventBehavior\Business\EventBehaviorFacadeInterface;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventBridge;
use Spryker\Zed\Kernel\RequestIdentifier;
use Spryker\Zed\Synchronization\Dependency\Client\SynchronizationToStorageClientBridge;
use Spryker\Zed\Synchronization\SynchronizationDependencyProvider;
use SprykerTest\Client\Testify\Helper\ClientHelperTrait;
use SprykerTest\Shared\Testify\Helper\AbstractHelper;
use SprykerTest\Shared\Testify\Helper\LocatorHelperTrait;
use SprykerTest\Zed\Testify\Helper\Business\BusinessHelperTrait;
use SprykerTest\Zed\Testify\Helper\Business\DependencyProviderHelperTrait;

class EventBehaviorHelper extends AbstractHelper
{
    use BusinessHelperTrait;
    use ClientHelperTrait;
    use LocatorHelperTrait;
    use DependencyProviderHelperTrait;

    /**
     * @param string $queueName
     *
     * @return void
     */
    public function consumeMessagesFromQueue(string $queueName): void
    {
        // Moved to QueueHelper
        $queueProxyMock = Stub::make(QueueProxy::class, [
            'getQueueAdapter' => function () {
                return $this->getQueueHelper()->getInMemoryQueueAdapter();
            },
        ]);

        $this->getClientHelper()->mockFactoryMethod(
            'createQueueProxy',
            $queueProxyMock,
            'Queue'
        );

        $queueClient = $this->getClientHelper()->getClient('Queue');

        $this->getLocatorHelper()->addToLocatorCache('queue-client', $queueClient);
        // Moved to QueueHelper


        $this->getBusinessHelper()->mockFactoryMethod(
            'getQueueClient',
            $queueClient,
            'Queue'
        );

        /** @var \Spryker\Zed\Queue\Business\QueueFacadeInterface $queueFacade */
        $queueFacade = $this->getBusinessHelper()->getFacade('Queue');

        $storageClientStub = Stub::make($this->getClientHelper()->getClient('Storage'), [
            'setMulti' => Stub\Expected::exactly(1, function (...$args) {
                $foo = 'bar';
            }),
        ]);

        $this->getDependencyProviderHelper()->setDependency(
            SynchronizationDependencyProvider::CLIENT_STORAGE,
            new SynchronizationToStorageClientBridge($storageClientStub)
        );

        $queueFacade->startTask($queueName);
    }

    /**
     * This method loads all data from the `spy_event_behavior_entity_change` table that's in the current request and
     * will push the data through the event module into the queue???
     *
     * @return void
     */
    public function triggerEvents(): void
    {
        $queueProxyMock = Stub::make(QueueProxy::class, [
            'getQueueAdapter' => function () {
                return $this->getQueueHelper()->getInMemoryQueueAdapter();
            },
        ]);

        $this->getClientHelper()->mockFactoryMethod(
            'createQueueProxy',
            $queueProxyMock,
            'Queue'
        );

        $queueClient = $this->getClientHelper()->getClient('Queue');

        $this->getBusinessHelper()->mockFactoryMethod(
            'getQueueClient',
            new EventToQueueBridge($queueClient),
            'Event'
        );

        $eventFacade = $this->getBusinessHelper()->getFacade('Event');

        $this->getBusinessHelper()->mockFactoryMethod(
            'getEventFacade',
            new EventBehaviorToEventBridge($eventFacade),
            'EventBehavior'
        );

        $this->getFacade()->triggerRuntimeEvents();
    }

    /**
     * @return \PyzTest\Zed\Availability\Helper\QueueHelper
     */
    protected function getQueueHelper(): QueueHelper
    {
        /** @var \PyzTest\Zed\Availability\Helper\QueueHelper $queueHelper */
        $queueHelper = $this->getModule('\\' . QueueHelper::class);

        return $queueHelper;
    }

    /**
     * Loads entities from `\Orm\Zed\EventBehavior\Persistence\Map\SpyEventBehaviorEntityChangeTableMap::TABLE_NAME` and
     * moves them to the event queue.
     *
     * @return void
     */
    public function triggerRuntimeEvents(): void
    {
        $this->getFacade()->triggerRuntimeEvents();
    }

    protected function getFacade(): EventBehaviorFacadeInterface
    {
        /** @var \Spryker\Zed\EventBehavior\Business\EventBehaviorFacadeInterface $facade */
        $facade = $this->getBusinessHelper()->getFacade('EventBehavior');

        return $facade;
    }

    /**
     * The EventBehavior adds methods to entities where this behavior is added to and saves a copy of the relevant data
     * in it's own database table.
     *
     * When an entity is saved the behavior creates a new entry in it's own database table. This table should have a
     * line added for the entity under test.
     *
     * Additionally, we assert that the `event` is named `Entity.your_entity.create`.
     *
     * @param string $entityName The name of the entity under test e.g. `spy_foo_bar`.
     *
     * @return void
     */
    public function assertEventBehaviorCreateEntryExistsFor(string $entityName): void
    {
        $eventData = $this->findEntityEventBehaviorChangeEntityData($entityName);

        $this->assertNotNull($eventData, $this->format(sprintf(
            '<fg=green>%s</> not found in the <fg=green>spy_event_behavior_entity_change</> table. To find out whats wrong debug <fg=yellow>YourEntity::saveEventBehaviorEntityChange()</> method.',
            $entityName
        )));

        $expectedEventName = sprintf('Entity.%s.create', $entityName, 'create');
        $this->assertSame($expectedEventName, $eventData['event'], $this->format(sprintf(
            '<fg=green>%s</> not found in the saved data for the <fg=green>%s</> entity. To find out whats wrong debug <fg=yellow>YourEntity::saveEventBehaviorEntityChange()</> method.',
            $expectedEventName,
            $entityName
        )));
    }

    /**
     * @param string $entityName
     *
     * @return array|null
     */
    protected function findEntityEventBehaviorChangeEntityData(string $entityName): ?array
    {
        $eventBehaviorEntityChangeEntityCollection = $this->findEntityEventBehaviorChangeEntityForCurrentRequest();

        if ($eventBehaviorEntityChangeEntityCollection->count() === 0) {
            return null;
        }

        foreach ($eventBehaviorEntityChangeEntityCollection as $spyEventBehaviorEntityChangeEntity) {
            $decodedData = json_decode($spyEventBehaviorEntityChangeEntity->getData(), true);

            if ($decodedData['name'] === $entityName) {
                return $decodedData;
            }
        }

        return null;
    }

    /**
     * @return \Propel\Runtime\Collection\ObjectCollection
     */
    protected function findEntityEventBehaviorChangeEntityForCurrentRequest(): ObjectCollection
    {
        $processId = RequestIdentifier::getRequestId();

        return SpyEventBehaviorEntityChangeQuery::create()->filterByProcessId($processId)->find();
    }
}
