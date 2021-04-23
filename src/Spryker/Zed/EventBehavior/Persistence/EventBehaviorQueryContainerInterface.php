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
     * @api
     *
     * @param string $processId
     *
     * @return \Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery
     */
    public function queryEntityChange($processId);

    /**
     * @api
     *
     * @param \DateTime $date
     *
     * @return \Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery
     */
    public function queryLatestEntityChange(DateTime $date);

    /**
     * @api
     *
     * @param int[] $keys
     *
     * @return \Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery
     */
    public function queryEntityByKeys(array $keys);

    /**
     * @api
     *
     * @deprecated This method is deprecated without replacement. It's not used by TriggerManager::triggerRuntimeEvents() anymore.
     *
     * @return bool
     */
    public function eventBehaviorTableExists();
}
