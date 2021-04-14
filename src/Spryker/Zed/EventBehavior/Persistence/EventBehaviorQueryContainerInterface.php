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
     * - Returns SpyEventBehaviorEntityChangeQuery that filters by a given `$processId`.
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
     * - Returns SpyEventBehaviorEntityChangeQuery that filters by a given `$date`.
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
     * - Returns true, when propel install was running with this package.
     * - Returns false, when propel install was not running with this package.
     *
     * @api
     *
     * @deprecated This method is deprecated without replacement. It's not used by TriggerManager::triggerRuntimeEvents() anymore.
     *
     * @return bool
     */
    public function eventBehaviorTableExists();
}
