<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business\Model;

interface EventResourceManagerInterface
{
    /**
     * @param array $resources
     * @param array $ids
     *
     * @return void
     */
    public function triggerResourceEvents(array $resources, array $ids = []);
}
