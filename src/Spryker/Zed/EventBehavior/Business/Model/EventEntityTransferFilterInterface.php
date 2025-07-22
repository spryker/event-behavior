<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Generated\Shared\Transfer\EventEntityRequestTransfer;
use Generated\Shared\Transfer\EventEntityResponseTransfer;

interface EventEntityTransferFilterInterface
{
    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     *
     * @return array
     */
    public function getEventTransferIds(array $eventTransfers);

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     * @param string $foreignKeyColumnName
     *
     * @return array
     */
    public function getEventTransferForeignKeys(array $eventTransfers, $foreignKeyColumnName);

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     * @param array<string> $columns
     *
     * @return array<\Generated\Shared\Transfer\EventEntityTransfer>
     */
    public function getEventTransfersByModifiedColumns(array $eventTransfers, array $columns);

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     * @param string $foreignKeyColumnName
     *
     * @return array
     */
    public function getGroupedEventTransferForeignKeysByForeignKey(array $eventTransfers, string $foreignKeyColumnName);

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     * @param string $columnName
     *
     * @return array
     */
    public function getEventTransfersOriginalValues(array $eventTransfers, string $columnName): array;

    /**
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventTransfers
     * @param string $columnName
     *
     * @return array
     */
    public function getEventTransfersAdditionalValues(array $eventTransfers, string $columnName): array;

    /**
     * @param \Generated\Shared\Transfer\EventEntityRequestTransfer $eventTransferRequestTransfer
     *
     * @return \Generated\Shared\Transfer\EventEntityResponseTransfer
     */
    public function getEventTransferValuesWithTimestamps(EventEntityRequestTransfer $eventTransferRequestTransfer): EventEntityResponseTransfer;
}
