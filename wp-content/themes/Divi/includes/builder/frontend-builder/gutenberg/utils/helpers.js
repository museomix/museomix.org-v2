import get from 'lodash/get';

const getHelpers = () => get(window, 'et_builder_gutenberg.helpers', {});
const getPostType = () => get(getHelpers(), 'postType', false);
const getPostID = () => get(getHelpers(), 'postID', false);
const getVBUrl = () => get(getHelpers(), 'vbUrl', false);
const isBuilderUsed = () => get(getHelpers(), 'builderUsed', false);
const isScriptDebug = () => get(getHelpers(), 'scriptDebug', false);
const i18n = () => get(getHelpers(), 'i18n', false);

export {
  getHelpers,
  getPostType,
  getPostID,
  getVBUrl,
  isBuilderUsed,
  isScriptDebug,
  i18n,
};
