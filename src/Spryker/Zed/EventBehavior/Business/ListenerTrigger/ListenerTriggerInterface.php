<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\ListenerTrigger;

interface ListenerTriggerInterface
{
    /**
     * @param string $eventListenerName
     * @param string $transferData
     * @param string $format
     *
     * @return void
     */
    public function triggerEventListenerByName(string $eventListenerName, string $transferData, string $format): void;
}
