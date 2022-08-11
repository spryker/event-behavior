<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\EventBehavior\Helper;

use Codeception\Module;
use SprykerTest\Shared\Testify\Helper\ModuleHelperTrait;

trait EventBehaviorHelperTrait
{
    use ModuleHelperTrait;

    /**
     * @return \SprykerTest\Zed\EventBehavior\Helper\EventBehaviorHelper
     */
    protected function getEventBehaviorHelper(): EventBehaviorHelper
    {
        /** @var \SprykerTest\Zed\EventBehavior\Helper\EventBehaviorHelper $eventBehaviorHelper */
        $eventBehaviorHelper = $this->getModule('\\' . EventBehaviorHelper::class);

        return $eventBehaviorHelper;
    }

    /**
     * @param string $name
     *
     * @return \Codeception\Module
     */
    //abstract protected function getModule(string $name): Module;
}
