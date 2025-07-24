<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Generated\Shared\Transfer\EventEntityTransfer;
use Generated\Shared\Transfer\HydrateEventsRequestTransfer;
use Generated\Shared\Transfer\HydrateEventsResponseTransfer;

class EventEntityTransferFilter implements EventEntityTransferFilterInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     *
     * @return array
     */
    public function getEventTransferIds(array $eventTransfers)
    {
        $ids = [];
        foreach ($eventTransfers as $eventTransfer) {
            $ids[] = $eventTransfer->getId();
        }

        return array_unique($ids);
    }

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     * @param string $foreignKeyColumnName
     *
     * @return array
     */
    public function getEventTransferForeignKeys(array $eventTransfers, $foreignKeyColumnName)
    {
        if (!$foreignKeyColumnName) {
            return [];
        }

        $foreignKeys = [];
        foreach ($eventTransfers as $eventTransfer) {
            if (!isset($eventTransfer->getForeignKeys()[$foreignKeyColumnName])) {
                continue;
            }

            $value = $eventTransfer->getForeignKeys()[$foreignKeyColumnName];
            if ($value !== null) {
                $foreignKeys[] = $value;
            }
        }

        return array_unique($foreignKeys);
    }

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     * @param string $foreignKeyColumnName
     *
     * @return array
     */
    public function getGroupedEventTransferForeignKeysByForeignKey(array $eventTransfers, string $foreignKeyColumnName)
    {
        if (!$foreignKeyColumnName) {
            return [];
        }

        $foreignKeys = [];
        foreach ($eventTransfers as $eventTransfer) {
            $eventTransferForeignKeys = $eventTransfer->getForeignKeys();

            if (!isset($eventTransferForeignKeys[$foreignKeyColumnName])) {
                continue;
            }
            $key = $eventTransferForeignKeys[$foreignKeyColumnName];
            $foreignKeys[$key][] = $eventTransfer->getForeignKeys();
        }

        return $foreignKeys;
    }

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     * @param array<string> $columns
     *
     * @return array<\Generated\Shared\Transfer\EventEntityTransfer>
     */
    public function getEventTransfersByModifiedColumns(array $eventTransfers, array $columns)
    {
        $validEventTransfers = [];
        foreach ($eventTransfers as $eventTransfer) {
            if ($this->checkColumnsExists($columns, $eventTransfer->getModifiedColumns())) {
                $validEventTransfers[] = $eventTransfer;
            }
        }

        return $validEventTransfers;
    }

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     * @param string $columnName
     *
     * @return array
     */
    public function getEventTransfersOriginalValues(array $eventTransfers, string $columnName): array
    {
        if (!$columnName) {
            return [];
        }

        $originalValues = [];
        foreach ($eventTransfers as $eventTransfer) {
            $originalValuesOfEvent = $eventTransfer->getOriginalValues();
            if (!isset($originalValuesOfEvent[$columnName])) {
                continue;
            }

            $originalValues[] = $originalValuesOfEvent[$columnName];
        }

        return array_unique($originalValues);
    }

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     * @param string $columnName
     *
     * @return array
     */
    public function getEventTransfersAdditionalValues(array $eventTransfers, string $columnName): array
    {
        if (!$columnName) {
            return [];
        }

        $additionalValues = [];
        foreach ($eventTransfers as $eventTransfer) {
            $additionalValuesOfEvent = $eventTransfer->getAdditionalValues();
            if (!isset($additionalValuesOfEvent[$columnName])) {
                continue;
            }

            $additionalValues[] = $additionalValuesOfEvent[$columnName];
        }

        return array_unique($additionalValues);
    }

    /**
     * @param \Generated\Shared\Transfer\HydrateEventsRequestTransfer $hydrateEventsRequestTransfer
     *
     * @return \Generated\Shared\Transfer\HydrateEventsResponseTransfer
     */
    public function hydrateEventDataTransfer(HydrateEventsRequestTransfer $hydrateEventsRequestTransfer): HydrateEventsResponseTransfer
    {
        $hydrateEventsResponseTransfer = new HydrateEventsResponseTransfer();
        $sortedEventTransfers = $hydrateEventsRequestTransfer->getEventEntities()->getArrayCopy();

        if (count($sortedEventTransfers) === 0) {
            return $hydrateEventsResponseTransfer;
        }
        usort($sortedEventTransfers, fn (EventEntityTransfer $a, EventEntityTransfer $b) => $a->getTimestamp() <=> $b->getTimestamp());

        $hydrateEventsResponseTransfer->setIdTimestampMap($this->getIdsTimestampMap($sortedEventTransfers));

        if ($hydrateEventsRequestTransfer->getAdditionalValueName()) {
            $hydrateEventsResponseTransfer->setAdditionalValueTimestampMap($this->getAdditionalValueTimestampMap($sortedEventTransfers, $hydrateEventsRequestTransfer->getAdditionalValueName()));
        }

        if ($hydrateEventsRequestTransfer->getForeignKeyName()) {
            $hydrateEventsResponseTransfer->setForeignKeyTimestampMap($this->getForeignKeyTimestampMap($sortedEventTransfers, $hydrateEventsRequestTransfer->getForeignKeyName()));
        }

        return $hydrateEventsResponseTransfer;
    }

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     *
     * @return array<int, int>
     */
    protected function getIdsTimestampMap(array $eventTransfers): array
    {
        $ids = [];
        foreach ($eventTransfers as $eventTransfer) {
            $ids[(int)$eventTransfer->getId()] = (int)$eventTransfer->getTimestamp();
        }

        return $ids;
    }

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     * @param string $columnName
     *
     * @return array<int|string, int>
     */
    protected function getAdditionalValueTimestampMap(array $eventTransfers, string $columnName): array
    {
        if (!$columnName) {
            return [];
        }

        $additionalValues = [];
        foreach ($eventTransfers as $eventTransfer) {
            $additionalValuesOfEvent = $eventTransfer->getAdditionalValues();
            if (!isset($additionalValuesOfEvent[$columnName])) {
                continue;
            }

            $additionalValues[$additionalValuesOfEvent[$columnName]] = (int)$eventTransfer->getTimestamp();
        }

        return $additionalValues;
    }

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     * @param string $foreignKeyColumnName
     *
     * @return array<int, int>
     */
    protected function getForeignKeyTimestampMap(array $eventTransfers, string $foreignKeyColumnName): array
    {
        if (!$foreignKeyColumnName) {
            return [];
        }

        $foreignKeys = [];
        foreach ($eventTransfers as $eventTransfer) {
            if (!isset($eventTransfer->getForeignKeys()[$foreignKeyColumnName])) {
                continue;
            }

            $value = $eventTransfer->getForeignKeys()[$foreignKeyColumnName] ?? null;
            if ($value !== null) {
                $foreignKeys[(int)$value] = (int)$eventTransfer->getTimestamp();
            }
        }

        return $foreignKeys;
    }

    /**
     * @param array<string> $columns
     * @param array<string> $modifiedColumns
     *
     * @return bool
     */
    protected function checkColumnsExists(array $columns, array $modifiedColumns)
    {
        foreach ($columns as $column) {
            if (in_array($column, $modifiedColumns)) {
                return true;
            }
        }

        return false;
    }
}
