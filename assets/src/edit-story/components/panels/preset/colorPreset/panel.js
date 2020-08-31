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
import styled, { css } from 'styled-components';
import { useRef } from 'react';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import PresetPanel from '../presetPanel';
import { areAllType, presetHasGradient, presetHasOpacity } from '../utils';
import WithTooltip from '../../../tooltip';
import { Remove } from '../../../../icons';
import { useStory } from '../../../../app/story';
import generatePatternStyles from '../../../../utils/generatePatternStyles';
import { useKeyDownEffect } from '../../../keyboard';

const PRESET_SIZE = 30;
const REMOVE_ICON_SIZE = 18;

const Transparent = styled.div`
  width: 100%;
  height: 100%;
  position: absolute;
  background-image: conic-gradient(
    #fff 0.25turn,
    #d3d4d4 0turn 0.5turn,
    #fff 0turn 0.75turn,
    #d3d4d4 0turn 1turn
  );
  background-size: 50% 50%;
`;

const ColorWrapper = styled.div`
  display: block;
  width: ${PRESET_SIZE}px;
  height: ${PRESET_SIZE}px;
  border: 1px solid ${({ theme }) => theme.colors.whiteout};
  border-radius: 100%;
  overflow: hidden;
  position: relative;
  ${({ disabled }) => (disabled ? 'opacity: 0.4;' : '')}

  &:focus-within {
    border-color: ${({ theme }) => theme.colors.fg.white};
    border-width: 3px;
  }
`;

const presetCSS = css`
  display: block;
  width: 100%;
  height: 100%;
  font-size: 13px;
  position: relative;
  cursor: pointer;
  background-color: transparent;
  border-color: transparent;
  border-width: 0;
  svg {
    width: ${REMOVE_ICON_SIZE}px;
    height: ${REMOVE_ICON_SIZE}px;
    position: absolute;
    top: calc(50% - ${REMOVE_ICON_SIZE / 2}px);
    left: calc(50% - ${REMOVE_ICON_SIZE / 2}px);
  }
`;

const ColorButton = styled.button.attrs({ type: 'button' })`
  ${presetCSS}
  ${({ color }) => generatePatternStyles(color)}

  &:focus {
    outline: none !important;
  }
`;

function Color({ onClick, children, ...rest }) {
  // We unfortunately have to manually assign this listener, as it would be default behaviour
  // if it wasn't for our listener further up the stack interpreting enter as "enter edit mode"
  // for text elements. For non-text element selection, this does nothing, that default beviour
  // wouldn't do.
  const ref = useRef();
  useKeyDownEffect(ref, 'enter', onClick, [onClick]);
  return (
    <ColorButton ref={ref} onClick={onClick} {...rest}>
      {children}
    </ColorButton>
  );
}

Color.propTypes = {
  children: PropTypes.node.isRequired,
  onClick: PropTypes.func.isRequired,
};

function ColorPresetPanel({ pushUpdate }) {
  const { currentPage, selectedElements } = useStory(
    ({ state: { currentPage, selectedElements } }) => {
      return {
        currentPage,
        selectedElements,
      };
    }
  );

  const isText = areAllType('text', selectedElements);
  const isBackground = selectedElements[0].id === currentPage.elements[0].id;
  const colorPresetRenderer = (
    color,
    i,
    activeIndex,
    handleOnClick,
    isEditMode
  ) => {
    if (!color) {
      return null;
    }
    const disabled =
      !isEditMode &&
      ((isBackground && presetHasOpacity(color)) ||
        (isText && presetHasGradient(color)));
    let tooltip = null;
    if (disabled) {
      // @todo The correct text here should be: Page background colors can not have an opacity.
      // However, due to bug with Tooltips/Popup, the text flows out of the screen.
      tooltip = isBackground
        ? __('Opacity not allowed for Page', 'web-stories')
        : __('Gradient not allowed for Text', 'web-stories');
    }
    return (
      <WithTooltip title={tooltip}>
        <ColorWrapper disabled={disabled}>
          <Transparent />
          <Color
            tabIndex={activeIndex === i ? 0 : -1}
            color={color}
            onClick={() => handleOnClick(color)}
            disabled={disabled}
            aria-label={
              isEditMode
                ? __('Delete color preset', 'web-stories')
                : __('Apply color preset', 'web-stories')
            }
          >
            {isEditMode && <Remove />}
          </Color>
        </ColorWrapper>
      </WithTooltip>
    );
  };

  return (
    <PresetPanel
      title={__('Saved colors', 'web-stories')}
      itemRenderer={colorPresetRenderer}
      pushUpdate={pushUpdate}
    />
  );
}

ColorPresetPanel.propTypes = {
  pushUpdate: PropTypes.func.isRequired,
};

export default ColorPresetPanel;
