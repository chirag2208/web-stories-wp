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
 * Internal dependencies
 */
import type { WeekdayIndex, Settings } from './types';

export const DEFAULT_DATE_SETTINGS: Settings = {
  dateFormat: 'F j, Y',
  timeFormat: 'g:i a',
  gmtOffset: 0,
  timezone: '',
  timezoneAbbr: '',
  weekStartsOn: 0 as WeekdayIndex,
};

let settings: Settings = {
  ...DEFAULT_DATE_SETTINGS,
};

/**
 * Reset date settings to their defaults.
 */
export function resetSettings() {
  settings = DEFAULT_DATE_SETTINGS;
}

/**
 * Update date settings.
 *
 * @param newSettings Date settings.
 */
export function updateSettings(newSettings: Partial<Settings>) {
  settings = {
    ...settings,
    ...newSettings,
  };
}

/**
 * Returns the current date settings.
 *
 * @return Date settings.
 */
export function getSettings() {
  return settings;
}
