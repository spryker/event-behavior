<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

use Generated\Shared\Transfer\EventTriggerResponseTransfer;

interface TriggerManagerInterface
{
    /**
     * @return \Generated\Shared\Transfer\EventTriggerResponseTransfer
     */
    public function triggerRuntimeEvents(): EventTriggerResponseTransfer;

    /**
     * @return void
     */
    public function triggerLostEvents();
}
