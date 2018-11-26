<?php
$id = $stream->id;
if (!isset($stream->filtercolor)) $stream->filtercolor = 'rgb(205, 205, 205)';
?>
<?php if( $stream->layout === 'list' && !empty($stream->wallwidth) ):?>
#ff-stream-<?php echo $id;?> {
    width: <?php echo $stream->wallwidth;?>px;
}
<?php endif;?>
<?php if( $stream->layout === 'list' ):?>
#ff-stream-<?php echo $id;?> .ff-stream-wrapper.ff-infinite > li {
margin: <?php echo $stream->wallvm;?>px <?php echo $stream->wallhm;?>px 0;
}
<?php endif;?>
#ff-stream-<?php echo $id;?> .ff-header h1,#ff-stream-<?php echo $id;?> .ff-controls-wrapper > span:hover { color: <?php echo $stream->headingcolor;?>; }
#ff-stream-<?php echo $id;?> .ff-controls-wrapper > span:hover { border-color: <?php echo $stream->headingcolor;?> !important; }
#ff-stream-<?php echo $id;?> .ff-header h2 { color: <?php echo $stream->subheadingcolor;?>; }
#ff-stream-<?php echo $id;?> .ff-filter-holder .ff-filter,
#ff-stream-<?php echo $id;?> .ff-filter-holder:before,
#ff-stream-<?php echo $id;?> .selectric,
#ff-stream-<?php echo $id;?> .ff-filter-holder .selectric-ff-filters-select .selectric-items,
#ff-stream-<?php echo $id;?> .ff-loadmore-wrapper .ff-btn:hover {
	background-color: <?php echo $stream->filtercolor;?>;
}
#ff-stream-<?php echo $id;?> .ff-filter:hover,
#ff-stream-<?php echo $id;?> .ff-filter.ff-filter--active,
#ff-stream-<?php echo $id;?> .ff-moderation-button,
#ff-stream-<?php echo $id;?> .ff-loadmore-wrapper .ff-btn,
#ff-stream-<?php echo $id;?> .ff-square:nth-child(1) {
	background-color: <?php echo $stream->headingcolor;?>;
}
#ff-stream-<?php echo $id;?> .ff-filter-holder .ff-search input {
	border-color: <?php echo $stream->filtercolor;?>;
}
#ff-stream-<?php echo $id;?> .ff-search input:focus,
#ff-stream-<?php echo $id;?> .ff-search input:hover {
border-color: <?php echo $stream->headingcolor;?>;
}

#ff-stream-<?php echo $id;?> .ff-filter-holder .ff-search:after {
	color: <?php echo $stream->filtercolor;?>;
}
#ff-stream-<?php echo $id;?> .selectric .button:before{
    border-top-color: <?php echo $stream->filtercolor;?>;
}
#ff-stream-<?php echo $id;?>, #ff-stream-<?php echo $id;?> .ff-popup,
#ff-stream-<?php echo $id;?> .ff-search input {
	background-color: <?php echo $stream->bgcolor;?>;
}

<?php if(strpos( $stream->bgcolor , 'rgba') !== false ):?>
#ff-stream-<?php echo $id;?> .ff-search input {
	background-color: <?php echo $stream->filtercolor;?>;
}
#ff-stream-<?php echo $id;?> .ff-search input,
#ff-stream-<?php echo $id;?> .ff-filter-holder .ff-search:after {
	color: #FFF;
}
<?php endif?>
<?php if( isset($stream->hidetext) && $stream->hidetext === 'yep' ):?>
#ff-stream-<?php echo $id;?> .ff-item:not(.ff-ad) .ff-content, #ff-stream-<?php echo $id;?> .ff-item:not(.ff-ad) h4, #ff-stream-<?php echo $id;?> .readmore-js-toggle {
	display: none !important;
}
#ff-stream-<?php echo $id;?> .ff-theme-flat.ff-style-3 .ff-content + .ff-item-meta {
	padding: 7px 0 20px;
}
<?php endif?>
<?php if( isset($stream->hidemeta) && $stream->hidemeta === 'yep' ):?>
#ff-stream-<?php echo $id;?> .ff-item-meta, .ff-theme-flat .ff-icon, .ff-theme-flat.ff-style-3 .ff-item-cont:before {
	display: none !important;
}
#ff-stream-<?php echo $id;?> .ff-theme-flat.ff-style-3 .ff-item-cont {
	padding-bottom: 15px;
}
#ff-stream-<?php echo $id;?> .ff-theme-flat .ff-img-holder + .ff-item-cont,
#ff-stream-<?php echo $id;?> .ff-theme-flat a + .ff-item-cont {
	margin-top: 0;
}
<?php endif?>
<?php if( isset($stream->hidetext) && $stream->hidetext === 'yep' && isset($stream->hidemeta) && $stream->hidemeta === 'yep' ):?>
#ff-stream-<?php echo $id;?> .ff-item-cont > .ff-img-holder:first-child {
	margin-bottom: 0;
}

#ff-stream-<?php echo $id;?> .ff-theme-flat .ff-item-cont {
	display: none;
}
<?php endif?>
#ff-stream-<?php echo $id;?> .ff-header h1, #ff-stream-<?php echo $id;?> .ff-header h2 {
	text-align: <?php echo $stream->hhalign;?>;
}
#ff-stream-<?php echo $id;?> .ff-controls-wrapper, #ff-stream-<?php echo $id;?> .ff-controls-wrapper > span {
	border-color: <?php echo $stream->filtercolor;?>;
}
#ff-stream-<?php echo $id;?> .ff-controls-wrapper, #ff-stream-<?php echo $id;?> .ff-controls-wrapper > span {
	color: <?php echo $stream->filtercolor;?>;
}

#ff-stream-<?php echo $id;?> .shuffle__sizer {
	margin-left: <?php echo $stream->margin;?>px !important;
}

#ff-stream-<?php echo $id;?> .picture-item__inner {
	background: <?php echo $stream->cardcolor;?>;
	color: <?php echo $stream->textcolor;?>;
	box-shadow: 0 1px 4px 0 <?php echo $stream->shadow;?>;
}

#ff-stream-<?php echo $id;?> .ff-content a {
	color: <?php echo $stream->linkscolor;?>;
}

#ff-stream-<?php echo $id;?>-slideshow .ff-share-popup, #ff-stream-<?php echo $id;?>-slideshow .ff-share-popup:after,
#ff-stream-<?php echo $id;?> .ff-share-popup, #ff-stream-<?php echo $id;?> .ff-share-popup:after {
	background: <?php echo $stream->cardcolor;?>;
}

#ff-stream-<?php echo $id;?> .ff-mob-link {
	background-color: <?php echo $stream->textcolor;?>;
}

#ff-stream-<?php echo $id;?> .ff-mob-link:after,
#ff-stream-<?php echo $id;?>-slideshow .ff-share-wrapper a:after {
	color: <?php echo $stream->cardcolor;?>;
}
#ff-stream-<?php echo $id;?>,
#ff-stream-<?php echo $id;?>-slideshow,
#ff-stream-<?php echo $id;?> .ff-infinite .ff-content {
	color: <?php echo $stream->textcolor;?>;
}
#ff-stream-<?php echo $id;?> .ff-infinite > li {
	background: <?php echo $stream->cardcolor;?>;
}
#ff-stream-<?php echo $id;?> .ff-square {
background: <?php echo ( ( $stream->bgcolor == 'rgb(255, 255, 255)' &&  $stream->cardcolor == 'rgb(255, 255, 255)' ) || strpos( $stream->bgcolor, ', 0)') !== false  ? 'rgb(205, 205, 205)' : $stream->cardcolor ) ;?>;
}
#ff-stream-<?php echo $id;?> .ff-icon, #ff-stream-<?php echo $id;?>-slideshow .ff-icon {
	border-color: <?php echo $stream->cardcolor;?>;
}
#ff-stream-<?php echo $id;?> .ff-style-2 .ff-icon:after {
	text-shadow: -1px 0 <?php echo $stream->cardcolor;?>, 0 1px <?php echo $stream->cardcolor;?>, 1px 0 <?php echo $stream->cardcolor;?>, 0 -1px <?php echo $stream->cardcolor;?>;
}

#ff-stream-<?php echo $id;?> .ff-item h1, #ff-stream-<?php echo $id;?> .ff-stream-wrapper.ff-infinite .ff-nickname, #ff-stream-<?php echo $id;?> h4, #ff-stream-<?php echo $id;?>-slideshow h4,#ff-stream-<?php echo $id;?>-slideshow h4 a,
#ff-stream-<?php echo $id;?> .ff-name, #ff-stream-<?php echo $id;?>-slideshow .ff-name {
	color: <?php echo $stream->namecolor;?> !important;
}

#ff-stream-<?php echo $id;?> .ff-mob-link:hover {
	background-color: <?php echo $stream->namecolor;?>;
}
#ff-stream-<?php echo $id;?> .ff-nickname,
#ff-stream-<?php echo $id;?> .ff-timestamp,
#ff-stream-<?php echo $id;?> .ff-item-bar,
#ff-stream-<?php echo $id;?> .ff-item-bar a {
	color: <?php echo $stream->restcolor;?> !important;
}
#ff-stream-<?php echo $id;?>-slideshow .ff-item-meta:before {
	background-color: <?php echo $stream->textcolor;?> !important;
}
#ff-stream-<?php echo $id;?> .ff-item, #ff-stream-<?php echo $id;?> .ff-stream-wrapper.ff-infinite .ff-content {
	text-align: <?php echo $stream->talign;?>;
}
#ff-stream-<?php echo $id;?> .ff-overlay {
	background-color: <?php echo $stream->bcolor;?>;
}

<?php if(isset($stream->mborder) && $stream->mborder == 'yep'):?>
#ff-stream-<?php echo $id;?> .picture-item__inner {
	border: 1px solid #eee;
}
<?php endif;?>
<?php
  if(!empty($stream->css)) echo stripslashes($stream->css);
?>