<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

class EventEntityTransferFilter implements EventEntityTransferFilterInterface
{
    /**
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
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
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
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
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
     * @param string $foreignKeyColumnName
     * @param string $relatedForeignKeyColumnName
     *
     * @return array
     */
    public function getGroupedEventTransferRelatedForeignKeys(array $eventTransfers, $foreignKeyColumnName, string $relatedForeignKeyColumnName)
    {
        if (!$foreignKeyColumnName) {
            return [];
        }

        $foreignKeys = [];
        foreach ($eventTransfers as $eventTransfer) {
            $eventTransferForeignKeys = $eventTransfer->getForeignKeys();

            if (!isset($eventTransferForeignKeys[$foreignKeyColumnName])
                || $eventTransferForeignKeys[$foreignKeyColumnName] === null
            ) {
                continue;
            }
            $key = $eventTransferForeignKeys[$foreignKeyColumnName];

            if (!array_key_exists($key, $foreignKeys)) {
                $foreignKeys[$key] = [];
            }
            if (array_key_exists($relatedForeignKeyColumnName, $eventTransferForeignKeys)
                && $eventTransferForeignKeys[$relatedForeignKeyColumnName] !== null
            ) {
                $foreignKeys[$key][] = $eventTransferForeignKeys[$relatedForeignKeyColumnName];
            }
        }

        return $foreignKeys;
    }

    /**
     * @param \Generated\Shared\Transfer\EventEntityTransfer[] $eventTransfers
     * @param array $columns
     *
     * @return \Generated\Shared\Transfer\EventEntityTransfer[]
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
     * @param array $columns
     * @param array $modifiedColumns
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
