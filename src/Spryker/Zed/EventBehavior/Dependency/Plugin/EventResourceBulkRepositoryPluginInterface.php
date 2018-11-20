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
     *  - Returns array of entity transfers according to offset and limit specified.
     *
     * @api
     *
     * @param int $offset
     * @param int $limit
     *
     * @return \Spryker\Shared\Kernel\Transfer\AbstractEntityTransfer[]
     */
    public function getData(int $offset, int $limit): array;
}
