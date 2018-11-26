<?php
/**
 * Flow-Flow
 *
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `FlowFlowAdmin.php`
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>

 * @link      http://looks-awesome.com
 * @copyright 2015 Looks Awesome
 */
session_start();

/** @noinspection PhpIncludeInspection */
require_once( dirname($_SERVER["SCRIPT_FILENAME"]) . '/LAClassLoader.php' );
LAClassLoader::get(dirname($_SERVER["SCRIPT_FILENAME"]) . '/')->register(true);

if (isset($_REQUEST['action'])){
	$context = ff_get_context();
	/** @var \flow\db\FFDBManager $db */
	$db = $context['db_manager'];

	global $facebookCache;
	$facebookCache = new flow\cache\FFFacebookCacheManager($context);

	$ff = flow\FlowFlow::get_instance($context);

	switch ($_REQUEST['action']) {
		case 'fetch_posts':
			$ff->processAjaxRequest();
			break;
		case 'load_cache':
			$ff->processAjaxRequestBackground();
			break;
		case 'refresh_cache':
			if (false !== ($time = $db->getOption('bg_task_time'))){
				if (time() > $time + 60){
					$ff->refreshCache();
					$time = time();
					$db->setOption('bg_task_time', $time);
					echo 'new cache time: ' . $time;
				}
			} else  {
			    $db->setOption('bg_task_time', time());
            }
			break;
		case 'flow_flow_save_stream_settings':
			$db->save_stream_settings();
			break;
		case 'flow_flow_get_stream_settings':
			$db->get_stream_settings();
			break;
		case 'flow_flow_ff_save_settings':
			$db->ff_save_settings_fn();
			break;
		case 'flow_flow_create_stream':
			$db->create_stream();
			break;
		case 'flow_flow_clone_stream':
			$db->clone_stream();
			break;
		case 'flow_flow_delete_stream':
			$db->delete_stream();
			break;
		case 'moderation_apply_action':
			$ff->moderation_apply();
			break;
		case 'flow_flow_social_auth':
			$db->social_auth();
			break;
		default:
			if (strpos($_REQUEST['action'], "backup") !== false) {
				$snapshotManager = new flow\settings\FFSnapshotManager($context);
				$snapshotManager->processAjaxRequest();
			}
			break;
	}
}
die;