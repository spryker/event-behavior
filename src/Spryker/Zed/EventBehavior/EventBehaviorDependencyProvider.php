<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior;

use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToEventBridge;
use Spryker\Zed\EventBehavior\Dependency\Facade\EventBehaviorToPropelFacadeBridge;
use Spryker\Zed\EventBehavior\Dependency\Service\EventBehaviorToUtilEncodingBridge;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

/**
 * @method \Spryker\Zed\EventBehavior\EventBehaviorConfig getConfig()
 */
class EventBehaviorDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const FACADE_EVENT = 'FACADE_EVENT';

    /**
     * @var string
     */
    public const FACADE_PROPEL = 'FACADE_PROPEL';

    /**
     * @var string
     */
    public const SERVICE_UTIL_ENCODING = 'UTIL_ENCODING_SERVICE';

    /**
     * @var string
     */
    public const PLUGINS_EVENT_TRIGGER_RESOURCE = 'PLUGINS_EVENT_TRIGGER_RESOURCE';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = $this->addEventFacade($container);
        $container = $this->addUtilEncodingService($container);
        $container = $this->addEventTriggerResourcePlugins($container);
        $container = $this->addPropelFacade($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addEventFacade(Container $container): Container
    {
        $container->set(static::FACADE_EVENT, function (Container $container) {
            return new EventBehaviorToEventBridge(
                $container->getLocator()->event()->facade(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addUtilEncodingService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_ENCODING, function (Container $container) {
            return new EventBehaviorToUtilEncodingBridge(
                $container->getLocator()->utilEncoding()->service(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addEventTriggerResourcePlugins(Container $container)
    {
        $container->set(static::PLUGINS_EVENT_TRIGGER_RESOURCE, function (Container $container) {
            return $this->getEventTriggerResourcePlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\EventBehavior\Dependency\Plugin\EventResourcePluginInterface>
     */
    protected function getEventTriggerResourcePlugins()
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPropelFacade(Container $container): Container
    {
        $container->set(static::FACADE_PROPEL, function (Container $container) {
            return new EventBehaviorToPropelFacadeBridge(
                $container->getLocator()->propel()->facade(),
            );
        });

        return $container;
    }
}
