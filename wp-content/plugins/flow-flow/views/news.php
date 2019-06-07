<?php if (!defined('WPINC')) die;
/**
 * Represents the view for the administration news page.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 * @link      http://looks-awesome.com
 * @copyright Looks Awesome
 */

$dbm = $context['db_manager'];
if (!$dbm->canCreateCssFolder()) {
    echo '<p class="ff-error" xmlns="http://www.w3.org/1999/html">Error: Plugin cannot create folder <strong>wp-content/resources/flow-flow/css</strong>, please add permissions or create this folder manually.</p>';
}
?>
<div class="news-page" id="news_page">
    <div class="news-loader"></div>
</div>