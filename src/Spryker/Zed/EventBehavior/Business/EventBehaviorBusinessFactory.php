<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Business;

use Spryker\Zed\EventBehavior\Business\ListenerTrigger\ListenerTrigger;
use Spryker\Zed\EventBehavior\Business\ListenerTrigger\ListenerTriggerInterface;
use Spryker\Zed\EventBehavior\Business\Model\EventEntityTransferFilter;
use Spryker\Zed\EventBehavior\Business\Model\EventResourcePluginResolver;
use Spryker\Zed\EventBehavior\Business\Model\EventResourcePluginResolverInterface;
use Spryker\Zed\EventBehavior\Business\Model\EventResourceQueryContainerManager;
use Spryker\Zed\EventBehavior\Business\Model\EventResourceRepositoryManager;
use Spryker\Zed\EventBehavior\Business\Model\TriggerManager;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToPropelFacadeInterface;
use Spryker\Zed\EventBehavior\EventBehaviorDependencyProvider;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\EventBehavior\EventBehaviorConfig getConfig()
 * @method \Spryker\Zed\EventBehavior\Persistence\EventBehaviorQueryContainerInterface getQueryContainer()
 */
class EventBehaviorBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\EventBehavior\Business\Model\TriggerManagerInterface
     */
    public function createTriggerManager()
    {
        return new TriggerManager(
            $this->getEventFacade(),
            $this->getUtilEncodingService(),
            $this->getQueryContainer(),
            $this->getConfig(),
            $this->getPropelFacade(),
            $this->getEntityManager()
        );
    }

    /**
     * @return \Spryker\Zed\EventBehavior\Business\ListenerTrigger\ListenerTriggerInterface
     */
    public function createListenerTrigger(): ListenerTriggerInterface
    {
        return new ListenerTrigger(
            $this->getEventFacade(),
            $this->getUtilEncodingService()
        );
    }

    /**
     * @return \Spryker\Zed\EventBehavior\Business\Model\EventResourceQueryContainerManager
     */
    public function createEventResourceQueryContainerManager(): EventResourceQueryContainerManager
    {
        return new EventResourceQueryContainerManager(
            $this->getEventFacade(),
            $this->getConfig()->getChunkSize()
        );
    }

    /**
     * @return \Spryker\Zed\EventBehavior\Business\Model\EventResourceRepositoryManager
     */
    public function createEventResourceRepositoryManager(): EventResourceRepositoryManager
    {
        return new EventResourceRepositoryManager(
            $this->getEventFacade(),
            $this->getConfig()->getChunkSize()
        );
    }

    /**
     * @return \Spryker\Zed\EventBehavior\Business\Model\EventResourcePluginResolverInterface
     */
    public function createEventResourcePluginResolver(): EventResourcePluginResolverInterface
    {
        return new EventResourcePluginResolver(
            $this->createEventResourceRepositoryManager(),
            $this->createEventResourceQueryContainerManager(),
            $this->getEventResourcePlugins(),
            $this->getConfig()
        );
    }

    /**
     * @return \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventInterface
     */
    public function getEventFacade()
    {
        return $this->getProvidedDependency(EventBehaviorDependencyProvider::FACADE_EVENT);
    }

    /**
     * @return \Spryker\Zed\EventBehavior\Dependency\Service\EventBehaviorToUtilEncodingInterface
     */
    public function getUtilEncodingService()
    {
        return $this->getProvidedDependency(EventBehaviorDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    /**
     * @return \Spryker\Zed\EventBehavior\Business\Model\EventEntityTransferFilterInterface
     */
    public function createEventEntityTransferFilter()
    {
        return new EventEntityTransferFilter();
    }

    /**
     * @return \Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface[]
     */
    protected function getEventResourcePlugins()
    {
        return $this->getProvidedDependency(EventBehaviorDependencyProvider::PLUGINS_EVENT_TRIGGER_RESOURCE);
    }

    /**
     * @return \Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToPropelFacadeInterface
     */
    public function getPropelFacade(): EventBehaviorToPropelFacadeInterface
    {
        return $this->getProvidedDependency(EventBehaviorDependencyProvider::FACADE_PROPEL);
    }
}
