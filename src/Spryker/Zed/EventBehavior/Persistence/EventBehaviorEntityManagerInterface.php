<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Persistence;


interface EventBehaviorEntityManagerInterface
{
    /**
     * Specification:
     * - Delete entities by primary keys ids
     *
     * @api
     *
     * @param int[] $primaryKeysIds
     *
     * @return int
     */
    public function deleteEventBehaviorEntityByPrimaryKeysIds(array $primaryKeysIds = []): int;
}
