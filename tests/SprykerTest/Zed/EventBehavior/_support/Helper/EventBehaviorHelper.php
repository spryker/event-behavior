<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\EventBehavior\Helper;

use Orm\Zed\EventBehavior\Persistence\Map\SpyEventBehaviorEntityChangeTableMap;
use Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery;
use Propel\Runtime\Collection\Collection;
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
     * Loads entities from `SpyEventBehaviorEntityChangeTableMap::TABLE_NAME` and adds them to the event queue.
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
    public function assertAtLeastOneEventBehaviorEntityChangeEntryExistsForEvent(string $eventName): void
    {
        $this->assertEventBehaviorEntryForEventExists($eventName);
    }

    /**
     * The EventBehavior adds methods to entities and saves a copy of the relevant data in it's own database table. This
     * table should have at least one line added for the `$eventName`.
     *
     * @param string $eventName
     *
     * @return void
     */
    protected function assertEventBehaviorEntryForEventExists(string $eventName): void
    {
        $eventData = $this->findEventBehaviorEntityChangeDataForEvent($eventName);

        $this->assertNotNull($eventData, $this->format(sprintf(
            'No data with event <fg=green>%s</> found in the <fg=green>spy_event_behavior_entity_change</> table. To find out whats wrong debug <fg=yellow>YourEntity::saveEventBehaviorEntityChange()</> method.',
            $eventName
        )));

        codecept_debug($this->format(sprintf(
            'Expected entry for <fg=green>%s</> event found in <fg=green>%s</> database table.',
            $eventName,
            SpyEventBehaviorEntityChangeTableMap::TABLE_NAME
        )));
    }

    /**
     * Returns all found entries with the given `$eventName` or null when no entry found.
     *
     * @param string $eventName
     *
     * @return array|null
     */
    protected function findEventBehaviorEntityChangeDataForEvent(string $eventName): ?array
    {
        $eventBehaviorEntityChangeCollection = $this->findEventBehaviorEntityChangeForCurrentRequest();

        if ($eventBehaviorEntityChangeCollection->count() === 0) {
            codecept_debug('Could not find any data for the current request in the database.');

            return null;
        }

        $eventBehaviorChangeEntityDataCollection = [];

        foreach ($eventBehaviorEntityChangeCollection as $eventBehaviorEntityChangeEntity) {
            $decodedData = json_decode($eventBehaviorEntityChangeEntity->getData(), true);

            if ($decodedData['event'] === $eventName) {
                $eventBehaviorChangeEntityDataCollection[] = $decodedData;
            }
        }

        if (count($eventBehaviorChangeEntityDataCollection) === 0) {
            $this->printDebugMessage($eventBehaviorEntityChangeCollection);

            return null;
        }

        return $eventBehaviorChangeEntityDataCollection;
    }

    /**
     * @param Collection $eventBehaviorEntityChangeCollection
     *
     * @return void
     */
    protected function printDebugMessage(Collection $eventBehaviorEntityChangeCollection): void
    {
        codecept_debug("\n" . $this->format(sprintf('The <fg=yellow>%s</> contains the following entries:', SpyEventBehaviorEntityChangeTableMap::TABLE_NAME)));

        foreach ($eventBehaviorEntityChangeCollection as $eventBehaviorEntityChangeEntity) {
            $decodedData = json_decode($eventBehaviorEntityChangeEntity->getData(), true);
            codecept_debug($this->format(sprintf('<fg=green>%s</> with <fg=yellow>%s</>', $decodedData['event'], $eventBehaviorEntityChangeEntity->getData())));
        }

        codecept_debug('');
    }

    /**
     * @return \Propel\Runtime\Collection\ObjectCollection
     */
    protected function findEventBehaviorEntityChangeForCurrentRequest(): ObjectCollection
    {
        $processId = RequestIdentifier::getRequestId();

        return SpyEventBehaviorEntityChangeQuery::create()->filterByProcessId($processId)->find();
    }
}
