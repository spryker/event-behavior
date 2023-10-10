<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\EventBehavior;

use Spryker\Glue\EventBehavior\Dependency\Facade\EventBehaviorToEventBehaviorFacadeInterface;
use Spryker\Glue\Kernel\AbstractFactory;

class EventBehaviorDependencyFactory extends AbstractFactory
{
    /**
     * @return \Spryker\Glue\EventBehavior\Dependency\Facade\EventBehaviorToEventBehaviorFacadeInterface
     */
    public function getEventBehaviorFacade(): EventBehaviorToEventBehaviorFacadeInterface
    {
        return $this->getProvidedDependency(EventBehaviorDependencyProvider::FACADE_EVENT_BEHAVIOR);
    }
}
