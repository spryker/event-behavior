<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business;

interface EventBehaviorFacadeInterface
{
    /**
     * Specification
     *  - Will find all entity change events with current processId from
     *  database and trigger them.
     *  - Deletes all triggered events from database.
     *
     * @api
     *
     * @return void
     */
    public function triggerRuntimeEvents();

    /**
     * Specification
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
     * @param array $columns
     *
     * @return \Generated\Shared\Transfer\EventEntityTransfer[]
     */
    public function getEventTransfersByModifiedColumns(array $eventTransfers, array $columns);

    /**
     * Specification:
     *  - Triggers events for specified resources.
     *
     * @api
     *
     * @param array $resources
     * @param array $ids
     *
     * @return void
     */
    public function executeResolvedPluginsBySources(array $resources, array $ids = []): void;

    /**
     * Specification:
     *  - Returns sorted resource names list from plugins configured in EventBehaviorDependencyProvider::getEventTriggerResourcePlugins().
     *
     * @api
     *
     * @return string[]
     */
    public function getAvailableResourceNames(): array;

    /**
     *
     * Specification:
     * - Returns an array of foreign keys grouped by column.
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
