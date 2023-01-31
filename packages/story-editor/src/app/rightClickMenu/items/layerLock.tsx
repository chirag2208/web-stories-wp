/*
 * Copyright 2022 Google LLC
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
import { ContextMenuComponents } from '@googleforcreators/design-system';
import { useCallback } from '@googleforcreators/react';

/**
 * Internal dependencies
 */
import { RIGHT_CLICK_MENU_LABELS } from '../constants';
import useStory from '../../story/useStory';

function LayerLock() {
  const updateSelectedElements = useStory(
    (ctx) => ctx.actions.updateSelectedElements
  );
  const toggleLayerLock = useCallback(
    () =>
      updateSelectedElements({
        properties: (oldElement) => ({
          ...oldElement,
          isLocked: !oldElement.isLocked,
        }),
      }),
    [updateSelectedElements]
  );

  return (
    <ContextMenuComponents.MenuButton onClick={toggleLayerLock}>
      {RIGHT_CLICK_MENU_LABELS.LOCK_UNLOCK}
    </ContextMenuComponents.MenuButton>
  );
}

export default LayerLock;
