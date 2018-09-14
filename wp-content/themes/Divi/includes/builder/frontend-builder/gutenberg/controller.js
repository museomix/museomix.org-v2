import React from 'react';
import placeholder from 'gutenberg/blocks/placeholder';
import { isBuilderUsed, isScriptDebug, getVBUrl } from 'gutenberg/utils/helpers';
import { DIVI, GUTENBERG } from 'gutenberg/constants';
import { registerBlock, unregisterBlock } from 'gutenberg/blocks/registration';
import get from 'lodash/get';
import throttle from 'lodash/throttle';
import isEmpty from 'lodash/isEmpty';
import startsWith from 'lodash/startsWith';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { dispatch, select, subscribe } from '@wordpress/data';
import { cold } from 'react-hot-loader';
import { RichText } from '@wordpress/editor';
import { renderToString, RawHTML } from '@wordpress/element';

// react-hot-loader replaces the Component with ProxyComponent altering its type.
// Since Gutenberg compares types while serializing content, we need to disable
// hot reloading for RichText and RawHTML
cold(RichText.Content);
cold(RawHTML);

const { setupEditor, editPost } = dispatch('core/editor');
const { isCleanNewPost, getCurrentPost, getEditedPostAttribute, getEditedPostContent, getBlocks } = select('core/editor');
const { getEditorMode } = select('core/edit-post');
const { switchEditorMode } = dispatch('core/edit-post');

const registerPlaceholder = () => registerBlock(placeholder);
const unregisterPlaceholder = () => unregisterBlock(placeholder);
const hasPlaceholder = () => {
  const blocks = getBlocks();
  if (blocks.length !== 1) {
    return false;
  }
  return get(blocks, '0.name') === placeholder.name;
};
// Throttle this just to avoid potential loops, better safe than sorry.
const switchToVisualMode = throttle(() => switchEditorMode('visual'), 100);

const button = renderToString((
  <div className="et-buttons">
    <button type="button" className="is-button is-default is-large components-button editor-post-switch-to-divi" data-editor={DIVI}>
      {__('Use The Divi Builder')}
    </button>
    <button type="button" className="is-button is-default is-large components-button editor-post-switch-to-gutenberg" data-editor={GUTENBERG}>
      {__('Return To Default Editor')}
    </button>
  </div>
));

class Controller {
  init = () => {
    registerPlaceholder();
    this.gbContent = '';
    this.unsubscribe = subscribe(this.onEditorContentChange);
    subscribe(this.onEditorModeChange);
  }

  onClick = (e) => {
    switch (e.target.getAttribute('data-editor')) {
      case DIVI: {
        this.addPlaceholder(getEditedPostContent());
        break;
      }
      default: {
        // Okay, this button was inside the placeholder but then was moved out of it.
        // That logic is a massive PITA, no time to refactor so this will have to do for now.
        jQuery('#et-switch-to-gutenberg').click();
      }
    }
  }

  addPlaceholder = (content = '') => {
    registerPlaceholder();
    this.gbContent = content;
    this.setupEditor(applyFilters('divi.addPlaceholder', content));
  }

  getGBContent = () => this.gbContent;

  setupEditor = (raw, title = false) => {
    const post = getCurrentPost();
    // Set post content
    setupEditor({ ...post, content: { raw } });
    if (title !== false && title !== post.title) {
      // Set post title
      editPost({ title });
    }
  }

  switchEditor = (editor, content) => {
    switch (editor) {
      case DIVI: {
        // Open VB
        window.location.href = getVBUrl();
        break;
      }
      default: {
        const title = getEditedPostAttribute('title');
        // Restore GB content and title (without saving)
        this.setupEditor(content, title);
        unregisterPlaceholder();
      }
    }
  }

  addButton = () => {
    // Add the custom button
    setTimeout(() => jQuery(button).on('click', 'button', this.onClick).insertAfter('.edit-post-header-toolbar'), 0);
  }

  onEditorContentChange = () => {
    const post = getCurrentPost();
    if (isEmpty(post) || !this.unsubscribe) {
      // If we don't have a post, GB isn't ready yet
      return;
    }
    this.addButton();
    // We only need to do this step once
    this.unsubscribe();
    this.unsubscribe = false;
    const content = get(post, 'content');
    if (startsWith(content, `<!-- ${placeholder.tag} `)) {
      // Post content already includes placeholder tag, nothing else to do
      return;
    }
    if (isBuilderUsed() || isCleanNewPost()) {
      // Add placeholder if post was edited with Divi
      if (isScriptDebug()) {
        // when SCRIPT_DEBUG is enabled, GB ends up calling `setupEditor` twice for whatever reason
        // and this causes our placeholder to be replaced with default GB content.
        // Until they fix their code, we need to ensure our own `setupEditor` is called last.
        setTimeout(() => this.addPlaceholder(content), 0);
      } else {
        this.addPlaceholder(content);
      }
    } else {
      // Unregister the placeholder block so it cannot be added via GB add block
      unregisterPlaceholder();
    }
  }

  onEditorModeChange = () => {
    const mode = getEditorMode();
    if (mode === 'text' && hasPlaceholder()) {
      switchToVisualMode();
    }
  }
}

const controller = new Controller();
const getGBContent = controller.getGBContent;
const switchEditor = controller.switchEditor;
controller.init();

export {
  getGBContent,
  switchEditor,
};
