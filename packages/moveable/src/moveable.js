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
import { forwardRef } from '@googleforcreators/react';
import OriginalMoveable from 'preact-moveable';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import InOverlay from './overlay';

const DEFAULT_Z_INDEX = 10;

function MoveableWithRef({ onContextMenu, ...moveableProps }, ref) {
  return (
    <InOverlay
      onContextMenu={onContextMenu}
      zIndex={DEFAULT_Z_INDEX}
      pointerEvents="initial"
      render={({ container }) => {
        return (
          <OriginalMoveable
            ref={ref}
            container={container}
            {...moveableProps}
          />
        );
      }}
    />
  );
}

const Moveable = forwardRef(MoveableWithRef);

Moveable.propTypes = {
  onContextMenu: PropTypes.func,
};

MoveableWithRef.propTypes = Moveable.propTypes;

export default Moveable;
