<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Persistence;

use DateTime;
use Spryker\Zed\Kernel\Persistence\QueryContainer\QueryContainerInterface;

interface EventBehaviorQueryContainerInterface extends QueryContainerInterface
{
    /**
     * Specification:
     * - TODO: Add method specification.
     *
     * @api
     *
     * @param string $processId
     *
     * @return \Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery
     */
    public function queryEntityChange($processId);

    /**
     * Specification:
     * - TODO: Add method specification.
     *
     * @api
     *
     * @param \DateTime $date
     *
     * @return \Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery
     */
    public function queryLatestEntityChange(DateTime $date);

    /**
     * Specification:
     * - Checks if the table exists
     *
     * @api
     *
     * @deprecated This method is deprecated without replacement. It's not used by TriggerManager::triggerRuntimeEvents() anymore.
     *
     * @return bool
     */
    public function eventBehaviorTableExists();
}
