<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Persistence;

use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;


class EventBehaviorEntityManager extends AbstractEntityManager implements EventBehaviorEntityManagerInterface
{
    /**
     * @inheriDoc
     *
     * @param int[] $primaryKeysIds
     *
     * @return int
     */
    public function deleteEventBehaviorEntityByPrimaryKeysIds(array $primaryKeysIds = []): int
    {
        return $this->getFactory()
            ->createEventBehaviorEntityChangeQuery()
            ->filterByPrimaryKeys($primaryKeysIds)
            ->delete();
    }
}
