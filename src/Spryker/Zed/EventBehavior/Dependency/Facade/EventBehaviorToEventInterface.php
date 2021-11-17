<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Dependency\Facade;

use Spryker\Shared\Kernel\Transfer\TransferInterface;

interface EventBehaviorToEventInterface
{
    /**
     * @param string $eventName
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     *
     * @return void
     */
    public function trigger($eventName, TransferInterface $transfer);

    /**
     * @param string $eventName
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $transfers
     *
     * @return void
     */
    public function triggerBulk($eventName, array $transfers): void;

    /**
     * @param string $listenerName
     * @param string $eventName
     * @param array<\Spryker\Shared\Kernel\Transfer\TransferInterface> $transfers
     *
     * @return void
     */
    public function triggerByListenerName(string $listenerName, string $eventName, array $transfers): void;
}
