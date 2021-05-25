<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business;

use Generated\Shared\Transfer\EventTriggerResponseTransfer;

interface EventBehaviorFacadeInterface
{
    /**
     * Specification:
     *  - Will find all entity change events with current processId from
     *  database and trigger them.
     *  - Deletes all triggered events from database.
     *  - Returns a EventTriggerResponseTransfer with debug information.
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\EventTriggerResponseTransfer
     */
    public function triggerRuntimeEvents(): EventTriggerResponseTransfer;

    /**
     * Specification:
     *  - Will find all expired/non-triggered entity change events from
     * database and trigger them.
     *  - Deletes all triggered events from database.
     *
     * @api
     *
     * @return void
     */
    public function triggerLostEvents();

    /**
     * Specification:
     *  - Returns Ids in eventTransfers.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
     *
     * @return array
     */
    public function getEventTransferIds(array $eventTransfers);

    /**
     * Specification:
     *  - Returns ForeignKeys in eventTransfers.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
     * @param string $foreignKeyColumnName
     *
     * @return array
     */
    public function getEventTransferForeignKeys(array $eventTransfers, $foreignKeyColumnName);

    /**
     * Specification:
     *  - Returns eventTransfers with matched modifiedColumns.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
     * @param string[] $columns
     *
     * @return \Generated\Shared\Transfer\EventEntityTransfer[]
     */
    public function getEventTransfersByModifiedColumns(array $eventTransfers, array $columns);

    /**
     * Specification:
     *  - Returns original value of the specficed column in eventTransfers.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
     * @param string $columnName
     *
     * @return array
     */
    public function getEventTransfersOriginalValues(array $eventTransfers, string $columnName): array;

    /**
     * Specification:
     *  - Returns field value of the specficed column in eventTransfers.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
     * @param string $columnName
     *
     * @return array
     */
    public function getEventTransfersAdditionalValues(array $eventTransfers, string $columnName): array;

    /**
     * Specification:
     *  - Triggers events for specified resources.
     *  - Accepts instances of EventResourceRepositoryPluginInterface and EventResourceBulkRepositoryPluginInterface as resources.
     *
     * @api
     *
     * @param string[] $resources
     * @param (string|int)[] $ids
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[] $resourcePublisherPlugins
     *
     * @return void
     */
    public function executeResolvedPluginsBySources(array $resources, array $ids = [], array $resourcePublisherPlugins = []): void;

    /**
     * Specification:
     *  - Returns sorted resource names list from plugins configured in EventBehaviorDependencyProvider::getEventTriggerResourcePlugins().
     *
     * @api
     *
     * @param \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[] $resourcePublisherPlugins
     *
     * @return string[]
     */
    public function getAvailableResourceNames(array $resourcePublisherPlugins = []): array;

    /**
     * Specification:
     * - Returns an array of foreign keys grouped by foreign key.
     *
     * @api
     *
     * @example ['foreignKey1Value' => ['relatedForeignKeys1' => [foreignKey1 => foreignKey1Value, ...], ...]]
     *
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
     * @param string $foreignKeyColumnName
     *
     * @return array
     */
    public function getGroupedEventTransferForeignKeysByForeignKey(array $eventTransfers, string $foreignKeyColumnName): array;

    /**
     * Specification:
     *  - Triggers events listener by it's name or|and event name.
     *  - The $transferData argument is used for filling up event entity transfer/transfers.
     *  - Format of $transferData should be defined in the third argument, like 'json'.
     *
     * @api
     *
     * @param string $eventListenerName
     * @param string $transferData
     * @param string $format
     * @param string $eventName
     *
     * @return void
     */
    public function triggerEventListenerByName(string $eventListenerName, string $transferData, string $format, string $eventName): void;
}
