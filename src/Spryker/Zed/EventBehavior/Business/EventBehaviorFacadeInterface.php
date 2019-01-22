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
     * - Returns an array of related foreign keys, grouped by foreign keys.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
     * @param string $foreignKeyColumnName
     * @param string $relatedForeignKeyColumnName
     *
     * @return array ['foreignKey' => ['relatedForeignKey1', 'relatedForeignKey2', ...]]
     */
    public function getGroupedEventTransferRelatedForeignKeys(array $eventTransfers, string $foreignKeyColumnName, string $relatedForeignKeyColumnName): array;
}
