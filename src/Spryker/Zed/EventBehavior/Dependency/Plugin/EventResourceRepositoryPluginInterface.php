<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Dependency\Plugin;

/**
 * @deprecated Use {@link \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourceBulkRepositoryPluginInterface} instead.
 */
interface EventResourceRepositoryPluginInterface extends EventResourcePluginInterface
{
    /**
     * Specification:
     *  - Returns query of resource entity, provided $ids parameter
     *    will apply to query to limit the result
     *
     * @api
     *
     * @param array<int> $ids
     *
     * @return array<\Spryker\Shared\Kernel\Transfer\AbstractEntityTransfer>
     */
    public function getData(array $ids = []): array;
}
