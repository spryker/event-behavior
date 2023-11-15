<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\EventBehavior;

use Spryker\Glue\EventBehavior\Dependency\Facade\EventBehaviorToEventBehaviorFacade;
use Spryker\Glue\Kernel\AbstractBundleDependencyProvider;
use Spryker\Glue\Kernel\Container;

class EventBehaviorDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const FACADE_EVENT_BEHAVIOR = 'FACADE_EVENT_BEHAVIOR';

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    public function provideDependencies(Container $container): Container
    {
        $container = parent::provideDependencies($container);
        $container = $this->addEventBehaviorFacade($container);

        return $container;
    }

    /**
     * @param \Spryker\Glue\Kernel\Container $container
     *
     * @return \Spryker\Glue\Kernel\Container
     */
    protected function addEventBehaviorFacade(Container $container): Container
    {
        $container->set(static::FACADE_EVENT_BEHAVIOR, function (Container $container) {
            return new EventBehaviorToEventBehaviorFacade(
                $container->getLocator()->eventBehavior()->facade(),
            );
        });

        return $container;
    }
}
