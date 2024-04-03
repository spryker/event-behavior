<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Persistence;

use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;

/**
 * @method \Spryker\Zed\EventBehavior\Persistence\EventBehaviorPersistenceFactory getFactory()
 */
class EventBehaviorEntityManager extends AbstractEntityManager implements EventBehaviorEntityManagerInterface
{
    /**
     * @param array<int> $primaryKeys
     *
     * @return int
     */
    public function deleteEventBehaviorEntityByPrimaryKeys(array $primaryKeys = []): int
    {
        return $this->getFactory()
            ->createEventBehaviorEntityChangeQuery()
            ->filterByPrimaryKeys($primaryKeys)
            ->delete();
    }

    /**
     * @param string $processId
     *
     * @return int
     */
    public function deleteEventBehaviorEntityByProcessId(string $processId): int
    {
        return $this->getFactory()
            ->createEventBehaviorEntityChangeQuery()
            ->filterByProcessId($processId)
            ->delete();
    }
}
