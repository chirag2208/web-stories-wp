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
import { identity, useContextSelector } from '@googleforcreators/react';

/**
 * Internal dependencies
 */
import type { SnackbarState } from '../../types/snackbar';
import Context from './context';

export function useSnackbar(): SnackbarState;
export function useSnackbar<T>(selector: (state: SnackbarState) => T): T;
export function useSnackbar<T>(
  selector: (state: SnackbarState) => T | SnackbarState = identity
) {
  return useContextSelector(Context, selector);
}
