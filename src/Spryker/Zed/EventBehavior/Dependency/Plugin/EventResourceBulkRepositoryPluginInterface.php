<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Dependency\Plugin;

interface EventResourceBulkRepositoryPluginInterface extends EventResourcePluginInterface
{
    /**
     * Specification:
     *  - Returns an array of transfers accordingly to specified offset and limit.
     *
     * @api
     *
     * @param int $offset
     * @param int $limit
     * @param array<int> $ids
     *
     * @return array<\Spryker\Shared\Kernel\Transfer\AbstractTransfer>
     */
    public function getData(int $offset, int $limit, array $ids = []): array;
}
