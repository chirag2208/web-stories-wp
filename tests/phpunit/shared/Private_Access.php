<?php

declare(strict_types = 1);

/**
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Web_Stories\Tests\Shared;

use ReflectionClass;
use ReflectionException;

/**
 * Trait Private_Access.
 *
 * Allows accessing private methods and properties for testing.
 *
 * @internal
 */
trait Private_Access {

	/**
	 * Call a private method as if it was public.
	 *
	 * @throws ReflectionException If the object could not be reflected upon.
	 *
	 * @param callable|array $callable Object instance or class string to call the method of.
	 * @param array          $args     Optional. Array of arguments to pass to the method.
	 * @return mixed Return value of the method call.
	 *
	 * @template C
	 * @template A
	 *
	 * @phpstan-param callable(A): C $callable
	 * @phpstan-param array<A> $args
	 * @phpstan-return C
	 */
	protected function call_private_method( $callable, array $args = [] ) {
		$method = ( new ReflectionClass( $callable[0] ) )->getMethod( $callable[1] );
		$method->setAccessible( true );
		return $method->invokeArgs( $callable[0], $args );
	}

	/**
	 * Call a private static method as if it was public.
	 *
	 * @throws ReflectionException If the class could not be reflected upon.
	 *
	 * @param callable|array $callable Object instance or class string to call the method of.
	 * @param array          $args     Optional. Array of arguments to pass to the method.
	 * @return mixed Return value of the method call.
	 *
	 * @template C
	 * @template A
	 *
	 * @phpstan-param array<A> $args
	 * @phpstan-param callable(A): C $callable
	 * @phpstan-return C
	 */
	protected function call_private_static_method( $callable, array $args = [] ) {
		$method = ( new ReflectionClass( $callable[0] ) )->getMethod( $callable[1] );
		$method->setAccessible( true );
		return $method->invokeArgs( null, $args );
	}

	/**
	 * Set a private property as if it was public.
	 *
	 * @throws ReflectionException If the object could not be reflected upon.
	 *
	 * @param object|string $object        Object instance or class string to set the property of.
	 * @param string        $property_name Name of the property to set.
	 * @param mixed         $value         Value to set the property to.
	 */
	protected function set_private_property( $object, string $property_name, $value ): void {
		$property = ( new ReflectionClass( $object ) )->getProperty( $property_name );
		$property->setAccessible( true );
		$property->setValue( $object, $value );
	}

	/**
	 * Get a private property as if it was public.
	 *
	 * @throws ReflectionException If the object could not be reflected upon.
	 *
	 * @param object|string $object        Object instance or class string to get the property of.
	 * @param string        $property_name Name of the property to get.
	 * @return mixed Return value of the property.
	 */
	protected function get_private_property( $object, string $property_name ) {
		$property = ( new ReflectionClass( $object ) )->getProperty( $property_name );
		$property->setAccessible( true );
		return $property->getValue( $object );
	}

	/**
	 * Get a static private property as if it was public.
	 *
	 * @throws ReflectionException If the class could not be reflected upon.
	 *
	 * @param string $class         Class string to get the property of.
	 * @param string $property_name Name of the property to get.
	 * @return mixed Return value of the property.
	 */
	protected function get_static_private_property( string $class, string $property_name ) {
		$properties = ( new ReflectionClass( $class ) )->getStaticProperties();
		return \array_key_exists( $property_name, $properties ) ? $properties[ $property_name ] : null;
	}
}
