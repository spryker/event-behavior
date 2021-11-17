<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Dependency\Facade;

use Spryker\Shared\Kernel\Transfer\TransferInterface;

class EventBehaviorToEventBridge implements EventBehaviorToEventInterface
{
    /**
     * @var \Spryker\Zed\Event\Business\EventFacadeInterface
     */
    protected $eventFacade;

    /**
     * @param \Spryker\Zed\Event\Business\EventFacadeInterface $eventFacade
     */
    public function __construct($eventFacade)
    {
        $this->eventFacade = $eventFacade;
    }

    /**
     * @param string $eventName
     * @param \Spryker\Shared\Kernel\Transfer\TransferInterface $transfer
     *
     * @return void
     */
    public function trigger($eventName, TransferInterface $transfer)
    {
        $this->eventFacade->trigger($eventName, $transfer);
    }

    /**
     * @param string $eventName
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $transfers
     *
     * @return void
     */
    public function triggerBulk($eventName, array $transfers): void
    {
        $this->eventFacade->triggerBulk($eventName, $transfers);
    }

    /**
     * @param string $listenerName
     * @param string $eventName
     * @param array<\Spryker\Shared\Kernel\Transfer\TransferInterface> $transfers
     *
     * @return void
     */
    public function triggerByListenerName(string $listenerName, string $eventName, array $transfers): void
    {
        $this->eventFacade->triggerByListenerName($listenerName, $eventName, $transfers);
    }
}
