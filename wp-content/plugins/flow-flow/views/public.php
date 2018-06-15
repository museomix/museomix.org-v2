<?php if ( ! defined( 'WPINC' ) ) die;
/**
 * Represents the view for the public-facing component of the plugin.
 *
 * This typically includes any information, if any, that is rendered to the
 * frontend of the theme when the plugin is activated.
 *
 * @package   FlowFlow
 * @author    Looks Awesome <email@looks-awesome.com>
 * @link      http://looks-awesome.com
 * @copyright 2014-2016 Looks Awesome
 */
$moderation = $context['moderation'] && $context['can_moderate'];
$stream = $context['stream'];
if (FF_USE_WP)
	$admin = $moderation ? $moderation : function_exists('current_user_can') && current_user_can('manage_options');
else
	$admin = ff_user_can_moderate();
$id = $stream->id;
$hash = $context['hashOfStream'];
$seo = $context['seo'];
$disableCache = isset($_REQUEST['disable-cache']);
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : '0';
?>
	<!-- Flow-Flow — Social stream plugin for WordPress -->
	<div class="ff-stream" data-plugin="flow_flow" id="ff-stream-<?php echo $id;?>"><span class="ff-loader"><span class="ff-square" ></span><span class="ff-square"></span><span class="ff-square ff-last"></span><span class="ff-square ff-clear"></span><span class="ff-square"></span><span class="ff-square ff-last"></span><span class="ff-square ff-clear"></span><span class="ff-square"></span><span class="ff-square ff-last"></span></span></div>

	<script type="text/javascript">
		(function ( $ ) {
			"use strict";
			var hash = '<?php echo $hash; ?>';
			if (/MSIE 8/.test(navigator.userAgent)) return;
			var opts = window.FlowFlowOpts;
            var isLS = isLocalStorageNameSupported();
			if (!opts) {
				window.console && window.console.log('Flow-Flow Social Stream: no options available on moment of script execution');
				return;
			}
			if (!window.FF_resource) {
				window.console && window.console.log('Flow-Flow Social Stream: required script has not been loaded. Please check if all resources in place or loaded in correct order.');
				return
			}
			var data = {
				'action': 'fetch_posts',
				'stream-id': '<?php echo $id;?>',
				'disable-cache': '<?php echo $disableCache;?>',
				'hash': hash,
				'page': '<?php echo $page;?>',
				'preview': '<?php echo $stream->preview ? 1 : 0;?>'
			};
			var isMobile = /android|blackBerry|iphone|ipad|ipod|opera mini|iemobile/i.test(navigator.userAgent);
			var streamOpts = <?php echo json_encode($stream); ?>;
            streamOpts.plugin = 'flow_flow';
            opts.streams['stream' + streamOpts.id] = streamOpts;
			var $cont = $("[data-plugin='flow_flow']#ff-stream-"+data['stream-id']);
			var ajaxDeferred;
			var script, style;
			var layout_pre = streamOpts.layout.charAt(0);
			var isOverlay = layout_pre === 'j' || streamOpts[layout_pre + '-overlay'] === 'yep';
			var imgIndex;
			if (isOverlay) {
				if (streamOpts.template[0] !== 'image') {
					for (var i = 0, len = streamOpts.template.length; i < len; i++) {
						if (streamOpts.template[i] === 'image') imgIndex = i;
					}
					streamOpts.template.splice(0, 0, streamOpts.template.splice(imgIndex, 1)[0]);
				}
				streamOpts.isOverlay = true;
			}
			if (FF_resource.scriptDeferred.state() === 'pending' && !FF_resource.scriptLoading) {
				script = document.createElement('script');
				script.src = "<?php echo $context['plugin_url'] . $context['slug'];?>/js/public.js";
				script.onload = function( script, textStatus ) {
					FF_resource.scriptDeferred.resolve();
				};
				document.body.appendChild(script);
				FF_resource.scriptLoading = true;
			}
			if (FF_resource.styleDeferred.state() === 'pending' && !FF_resource.styleLoading) {
				style = document.createElement('link');
				style.type = "text/css";
                style.id = "ff_style";
                style.rel = "stylesheet";
				style.href = "<?php echo $context['plugin_url'] . $context['slug'];?>/css/public.css";
				style.media = "screen";
				style.onload = function( script, textStatus ) {
					FF_resource.styleDeferred.resolve();
				};
				document.getElementsByTagName("head")[0].appendChild(style);
				FF_resource.styleLoading = true;
			}
			$cont.addClass('ff-layout-' + streamOpts.layout);
			if (!isMobile) $cont.css('minHeight', '1000px');
			ajaxDeferred = <?php if ($admin) {echo '$.get(opts.ajaxurl, data)';} else {echo 'isLS && sessionStorage.getItem(hash) ? {} : $.get(opts.ajaxurl, data)';} echo PHP_EOL; ?>;
			$.when( ajaxDeferred, FF_resource.scriptDeferred, FF_resource.styleDeferred ).done(function ( data ) {
				var response, $errCont, err;
                var moderation = <?php echo $moderation ? 1 : 0 ?>;
                var original = <?php if ($admin) {echo 'data[0]';} else {echo '(isLS && sessionStorage.getItem(hash)) ? sessionStorage.getItem(hash) : data[0]';}?>;
				try {
					response = JSON.parse(original);
				} catch (e) {
					window.console && window.console.log('Flow-Flow gets invalid data from server');
					if (opts.isAdmin || opts.isLog) {
						$errCont = $('<div class="ff-errors" id="ff-errors-invalid-response"><div class="ff-disclaim">If you see this message then you have administrator permissions and Flow-Flow got invalid data from server. Please provide error message below if you are doing support request.<\/div><div class="ff-err-info"><\/div><\/div>');
						$cont.before($errCont);
						$errCont.find('.ff-err-info').html(original == '' ? 'Empty response from server' : original)
					}
					return;
				}
                opts.streams['stream' + streamOpts.id]['items'] = response;
                if (!FlowFlowOpts.dependencies) FlowFlowOpts.dependencies = {};
				<?php
				$dependencies = apply_filters('ff_plugin_dependencies', array());
				foreach ($dependencies as $name) {
				    echo "if (!FlowFlowOpts.dependencies['{$name}']) FlowFlowOpts.dependencies['{$name}'] = true;";
				}
				?>
                var requests = [];
                var request, extension, style;

                for ( extension in FlowFlowOpts.dependencies ) {
                    if ( FlowFlowOpts.dependencies[extension] && FlowFlowOpts.dependencies[extension] !== 'loaded') {
                        request = $.getScript( opts.plugin_base + '-' + extension + '/js/ff_' + extension + '_public.js');
                        requests.push(request);

                        style = document.createElement('link');
                        style.type = "text/css";
                        style.rel = "stylesheet";
                        style.id = "ff_ad_style";
                        style.href = opts.plugin_base + '-' + extension + '/css/ff_' + extension + '_public.css';
                        style.media = "screen";
                        document.getElementsByTagName("head")[0].appendChild(style);

                        FlowFlowOpts.dependencies[extension] = 'loaded';
                    }
                }

                var resourcesLoaded = $.when.apply($, requests);

                resourcesLoaded.done(function(){
                    var $stream, width;
                    console.log(response);
                    $stream = FlowFlow.buildStreamWith(response, streamOpts, moderation, FlowFlowOpts.dependencies);
                    <?php if (!$admin) {echo 'if (isLS && response.items.length > 0 && response.hash.length > 0) sessionStorage.setItem(response.hash, original);' . PHP_EOL;}?>
                    var num = streamOpts.layout === 'compact' || (streamOpts.mobileslider === 'yep' && isMobile)? (streamOpts.mobileslider === 'yep' ? 3 : streamOpts['cards-num']) : false;
					$cont.append($stream);
                    if (typeof $stream !== 'string') {
                        FlowFlow.setupGrid($cont.find('.ff-stream-wrapper'), num, streamOpts.scrolltop === 'yep', streamOpts.gallery === 'yep', streamOpts, $cont);
                    }
                    setTimeout(function(){
                        $cont.find('.ff-header').removeClass('ff-loading').end().find('.ff-loader').addClass('ff-squeezed').delay(300).hide();
                    }, 0);

	                <?php do_action('ff_add_view_action', $stream);?>

                }).fail(function(){
                    console.log('Flow-Flow: resource loading failed')
                });

                var isErr = response.status === "errors";
                if ((opts.isAdmin || opts.isLog) && isErr) {
					$errCont = $('<div class="ff-errors" id="ff-errors-'+response.id+'"><div class="ff-err-info">If you see this then you are administrator and Flow-Flow got errors from APIs while requesting data. Please go to plugin admin and after refreshing page check for error(s) on stream settings page. Please provide error message info if you are doing support request.<\/div><\/div>');
					$cont.before($errCont);
				}

                if (opts.isAdmin && response.status === 'building') {
                    window.console && window.console.log(response);
                    $cont.prepend($('<div id="ff-admin-info">ADMIN INFO: Feeds cache is being built in background. Please wait for changes to apply. Page reload is required.<\/div>'));
                }
			});

            function isLocalStorageNameSupported() {
                var testKey = 'test', storage = window.sessionStorage;
                try {
                    storage.setItem(testKey, '1');
                    storage.removeItem(testKey);
                    return true;
                } catch (error) {
                    return false;
                }
            };

			return false;
		}(jQuery));
	</script>
	<!-- Flow-Flow — Social streams plugin for Wordpress -->