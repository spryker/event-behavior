<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Communication\Plugin\EventDispatcher;

use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\EventDispatcher\EventDispatcherInterface;
use Spryker\Shared\EventDispatcherExtension\Dependency\Plugin\EventDispatcherPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @method \Spryker\Zed\EventBehavior\EventBehaviorConfig getConfig()
 * @method \Spryker\Zed\EventBehavior\Persistence\EventBehaviorQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\EventBehavior\Business\EventBehaviorFacadeInterface getFacade()
 * @method \Spryker\Zed\EventBehavior\Communication\EventBehaviorCommunicationFactory getFactory()
 */
class EventBehaviorEventDispatcherPlugin extends AbstractPlugin implements EventDispatcherPluginInterface
{
    /**
     * @api
     *
     * @param \Spryker\Shared\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Spryker\Service\Container\ContainerInterface $container
     *
     * @return \Spryker\Shared\EventDispatcher\EventDispatcherInterface
     */
    public function extend(EventDispatcherInterface $eventDispatcher, ContainerInterface $container): EventDispatcherInterface
    {
        $eventDispatcher->addListener(KernelEvents::TERMINATE, function () {
            $this->getFacade()->triggerRuntimeEvents();
        });

        return $eventDispatcher;
    }
}
