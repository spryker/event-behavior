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
        $keys = array_chunk($primaryKeys, 100);
        $count = 0;
        foreach ($keys as $chunk) {
            $count += $this->getFactory()
                ->createEventBehaviorEntityChangeQuery()
                ->filterByPrimaryKeys($chunk)
                ->delete();
        }

        return $count;
    }

    /**
     * @param string $processId
     * @throws \Propel\Runtime\Exception\PropelException
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
