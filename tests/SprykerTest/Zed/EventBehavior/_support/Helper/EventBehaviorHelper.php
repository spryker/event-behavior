<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\EventBehavior\Helper;

use Orm\Zed\EventBehavior\Persistence\Map\SpyEventBehaviorEntityChangeTableMap;
use Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery;
use Propel\Runtime\Collection\ObjectCollection;
use Spryker\Zed\EventBehavior\Business\EventBehaviorFacadeInterface;
use Spryker\Zed\Kernel\RequestIdentifier;
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
     * Loads entities from `\Orm\Zed\EventBehavior\Persistence\Map\SpyEventBehaviorEntityChangeTableMap::TABLE_NAME` and
     * moves them to the event queue.
     *
     * @return void
     */
    public function triggerRuntimeEvents(): void
    {
        $this->getFacade()->triggerRuntimeEvents();
    }

    /**
     * @return \Spryker\Zed\EventBehavior\Business\EventBehaviorFacadeInterface
     */
    protected function getFacade(): EventBehaviorFacadeInterface
    {
        /** @var \Spryker\Zed\EventBehavior\Business\EventBehaviorFacadeInterface $facade */
        $facade = $this->getBusinessHelper()->getFacade('EventBehavior');

        return $facade;
    }

    /**
     * @param string $eventName
     *
     * @return void
     */
    public function assertEventBehaviorEntryExistsForEvent(string $eventName): void
    {
        $this->assertEventBehaviorEntryForEvent($tableName, 'create');
    }

    /**
     * @param string $tableName
     *
     * @return void
     */
    public function assertEventBehaviorCreateEntryExistsFor(string $tableName): void
    {
        $this->assertEventBehaviorEntryForEvent($tableName, 'create');
    }

    /**
     * @param string $tableName
     *
     * @return void
     */
    public function assertEventBehaviorUpdateEntryExistsFor(string $tableName): void
    {
        $this->assertEventBehaviorEntryForEvent($tableName, 'update');
    }

    /**
     * @param string $tableName
     *
     * @return void
     */
    public function assertEventBehaviorDeleteEntryExistsFor(string $tableName): void
    {
        $this->assertEventBehaviorEntryForEvent($tableName, 'delete');
    }

    /**
     * The EventBehavior adds methods to entities and saves a copy of the relevant data in it's own database table. This
     * table should have a line added for the entity under test.
     *
     * Additionally, we assert that the `event` is named `Entity.your_table_name.delete`.
     *
     * @param string $tableName The name of the entity under test e.g. `spy_foo_bar`.
     * @param string $eventSuffix The suffix for the expected eventName of the entity under test e.g. `spy_foo_bar`.
     *
     * @return void
     */
    protected function assertEventBehaviorEntryForEvent(string $tableName, string $eventSuffix): void
    {
        $expectedEventName = sprintf('Entity.%s.%s', $tableName, $eventSuffix);
        $eventData = $this->findEntityEventBehaviorChangeEntityData($tableName, $expectedEventName);

        $this->assertNotNull($eventData, $this->format(sprintf(
            '<fg=green>%s</> not found in the <fg=green>spy_event_behavior_entity_change</> table. To find out whats wrong debug <fg=yellow>YourEntity::saveEventBehaviorEntityChange()</> method.',
            $tableName
        )));

        $this->assertSame($expectedEventName, $eventData['event'], $this->format(sprintf(
            '<fg=yellow>%s</> not found in the saved data for the <fg=green>%s</> entity. To find out whats wrong debug <fg=yellow>YourEntity::saveEventBehaviorEntityChange()</> method.',
            $expectedEventName,
            $tableName
        )));

        codecept_debug($this->format(sprintf(
            'Expected entry for <fg=green>%s</> found in <fg=green>%s</> and has expected <fg=green>%s</> event name.',
            $tableName,
            SpyEventBehaviorEntityChangeTableMap::TABLE_NAME,
            $expectedEventName
        )));
    }

    /**
     * @param string $entityName
     * @param string $eventName
     *
     * @return array|null
     */
    protected function findEntityEventBehaviorChangeEntityData(string $entityName, string $eventName): ?array
    {
        $eventBehaviorEntityChangeEntityCollection = $this->findEntityEventBehaviorChangeEntityForCurrentRequest();

        if ($eventBehaviorEntityChangeEntityCollection->count() === 0) {
            return null;
        }

        foreach ($eventBehaviorEntityChangeEntityCollection as $spyEventBehaviorEntityChangeEntity) {
            $decodedData = json_decode($spyEventBehaviorEntityChangeEntity->getData(), true);

            if ($decodedData['name'] === $entityName && $decodedData['event'] === $eventName) {
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
