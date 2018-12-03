<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Persistence;

use Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery;
use Spryker\Zed\EventBehavior\Persistence\Exception\EventBehaviorQueryNotExistsException;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;

/**
 * @method \Spryker\Zed\EventBehavior\EventBehaviorConfig getConfig()
 * @method \Spryker\Zed\EventBehavior\Persistence\EventBehaviorQueryContainerInterface getQueryContainer()
 */
class EventBehaviorPersistenceFactory extends AbstractPersistenceFactory
{
    /**
     * @throws \Spryker\Zed\EventBehavior\Persistence\Exception\EventBehaviorQueryNotExistsException
     *
     * @return \Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery
     */
    public function createEventBehaviorEntityChangeQuery()
    {
        if (!class_exists(SpyEventBehaviorEntityChangeQuery::class)) {
            throw new EventBehaviorQueryNotExistsException();
        }
        return SpyEventBehaviorEntityChangeQuery::create();
    }
}
