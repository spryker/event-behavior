<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Dependency\Plugin;

interface EventResourceForeignKeysRepositoryExtensionPluginInterface
{
    /**
     * Specification:
     *  - Returns map for extracting foreign keys from transfers
     *  - Example ['spy_enity.column_name' => 'transfer_property_name']
     *
     * @api
     *
     * @return array<string, string>
     */
    public function getForeignKeysMapping(): array;
}
