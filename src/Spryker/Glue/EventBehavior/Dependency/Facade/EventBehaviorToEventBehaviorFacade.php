<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\EventBehavior\Dependency\Facade;

class EventBehaviorToEventBehaviorFacade implements EventBehaviorToEventBehaviorFacadeInterface
{
    /**
     * @var \Spryker\Zed\EventBehavior\Business\EventBehaviorFacadeInterface
     */
    protected $eventBehaviorFacade;

    /**
     * @param $eventBehaviorFacade \Spryker\Zed\EventBehavior\Business\EventBehaviorFacadeInterface
     */
    public function __construct($eventBehaviorFacade)
    {
        $this->eventBehaviorFacade = $eventBehaviorFacade;
    }

    /**
     * @return void
     */
    public function triggerRuntimeEvents(): void
    {
        $this->eventBehaviorFacade->triggerRuntimeEvents();
    }
}
