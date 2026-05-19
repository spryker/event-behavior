<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\EventBehavior\Helper;

use ReflectionProperty;
use Spryker\Service\Container\Container;
use SprykerTest\Service\Container\Helper\ContainerHelper as SprykerContainerHelper;

/**
 * Overrides spryker/container ContainerHelper to fix PHP 8.3+ deprecations:
 * - ReflectionProperty::setValue() requires two arguments for static properties
 * - Avoids autoloading ContainerDelegator when symfony/dependency-injection is absent
 */
class ContainerHelper extends SprykerContainerHelper
{
    protected function resetStaticProperties(): void
    {
        $staticProperties = [
            'aliases',
            'globalServices',
            'globalServiceIdentifier',
            'globalFrozenServices',
        ];

        foreach ($staticProperties as $staticProperty) {
            $reflectedProperty = new ReflectionProperty(Container::class, $staticProperty);
            $reflectedProperty->setAccessible(true);
            $reflectedProperty->setValue(null, []);
        }
    }

    protected function resetContainerDelegator(): void
    {
        // Use $autoload=false to avoid triggering class loading that can fail
        // when symfony/dependency-injection is not installed as a direct dependency.
        if (
            !interface_exists('Symfony\Component\DependencyInjection\ContainerInterface', false)
            || !class_exists('Spryker\Service\Container\ContainerDelegator', false)
        ) {
            return;
        }

        $reflectedProperty = new ReflectionProperty('Spryker\Service\Container\ContainerDelegator', 'instance');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue(null, null);
    }
}
