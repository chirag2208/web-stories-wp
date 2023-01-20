/*
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

/**
 * External dependencies
 */
import type { Element } from '@googleforcreators/elements';

/**
 * Internal dependencies
 */
import { MULTIPLE_VALUE } from '../../constants';
import type { ElementUpdater } from '../../types';

function updateProperties<E extends Element = Element>(
  currentProperties: E,
  newPropertiesOrUpdater: Partial<E> | ElementUpdater,
  commitValues: boolean
): Partial<E> {
  const newProperties =
    typeof newPropertiesOrUpdater === 'function'
      ? newPropertiesOrUpdater(currentProperties)
      : newPropertiesOrUpdater;
  if (!newProperties) {
    return {};
  }

  let updatedKeys = Object.keys(newProperties);
  // Always filter out "multi" values since they can be easily recalculated at
  // any time.
  updatedKeys = updatedKeys.filter(
    (key) => newProperties[key as keyof typeof newProperties] !== MULTIPLE_VALUE
  );
  // Only filter out the empty values at the commit time since an empty value
  // is a valid intermediary value.
  if (commitValues) {
    updatedKeys = updatedKeys.filter(
      (key) => newProperties[key as keyof typeof newProperties] !== ''
    );
  }
  if (updatedKeys.length === 0) {
    // Of course abort if no keys have a value
    return {};
  }
  return Object.fromEntries(
    updatedKeys.map((key) => [
      key,
      newProperties[key as keyof typeof newProperties],
    ])
  ) as Partial<E>;
}

export default updateProperties;
