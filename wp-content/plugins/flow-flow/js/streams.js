var ff_templates = {
    streamRow:      '<td class="controls"><div class="loader-wrapper"><div class="throbber-loader"></div></div><i class="flaticon-pen"></i> <i class="flaticon-copy"></i> <i class="flaticon-trash"></i></td> <td class="td-name"><%= name %></td> <td class="td-type"><%= layout %></span></td> <td class="td-feed"><%= feeds %></td><td><span class="cache-status-<%= status %>"></span></td><td><span class="shortcode">[ff id="<%= id %>"]</span><span class="shortcode-copy">Copy</span></td>',
    streamRowEmpty: '<tr class="empty-row"><td class="empty-cell" colspan="6">Please create at least one stream</td></tr>',
    listRowEmpty: '<tr><td  class="empty-cell" colspan="4">Add at least one feed</td></tr>',

    view:           '<input type="hidden" name="stream-<%= id %>-id" class="stream-id-value" value="<%= id %>"/>\
                <div class="section clearfix" id="stream-name-<%= id %>">\
                    <h1 class="float-left"><%= header %><span class="admin-button grey-button button-go-back">Go back to list</span></h1>\
                    <p class="float-left input-not-obvious"><input type="text" name="stream-<%= id %>-name" placeholder="Type name and hit Enter..."/>\
                    <ul class="view-tabs float-left"><li class="tab-cursor"></li><li data-tab="source">source</li><li data-tab="general">general</li><%= TVtab %><li data-tab="grid">grid</li><li data-tab="stylings">stylings</li><li data-tab="css">css</li><li data-tab="shortcode">shortcode</li></ul>\
                </div>\
                <div class="section" id="stream-feeds-<%= id %>" data-tab="source">\
                    <input type="hidden" name="stream-<%= id %>-feeds"/>\
                    <h1 class="desc-following">Connected feeds</h1>\
                    <p class="desc">Here you can connect feeds created on <a class="ff-pseudo-link" href="#sources-tab">Feeds tab</a>. To detach feed click feed label.</p>\
        <div class="stream-feeds">\
            <div class="stream-feeds__list"></div>\
            <div class="stream-feeds__block"><span class="stream-feeds__add">+ Connect feed to stream</span></div>\
            <div class="stream-feeds__select"><select></select><span class="stream-feeds__btn stream-feeds__ok"><i class="flaticon-plus"></i></span><span class="stream-feeds__btn stream-feeds__close"><i class="flaticon-cross"></i></span></div>\
        </div>\
    </div>\
    <div class="section"  data-tab="general" id="stream-settings-<%= id %>">\
        <h1>Stream general settings</h1>\
        <dl class="section-settings section-compact">\
                <dt>Items order\
                <p class="desc">Choose rule how stream sorts posts.<br>Proportional sorting guarantees that all networks are always present on first load.</p>\
                </dt>\
                <dd>\
                    <input id="stream-<%= id %>-smart-date-order" type="radio" name="stream-<%= id %>-order" checked value="smartCompare"/>\
                    <label for="stream-<%= id %>-smart-date-order">Proportional by date</label><br><br>\
                    <input id="stream-<%= id %>-date-order" type="radio" name="stream-<%= id %>-order" value="compareByTime"/>\
                    <label for="stream-<%= id %>-date-order">Strictly by date</label><br><br>\
                        <input id="stream-<%= id %>-random-order" type="radio" name="stream-<%= id %>-order" value="randomCompare"/>\
                        <label for="stream-<%= id %>-random-order">Random</label>\
                    </dd>\
                        <dt class="hidden">Load last\
                            <p class="desc">Number of items that is pulled and cached from each connected feed. Be aware that some APIs can ignore this setting.</p>\
                        </dt>\
                        <dd class="hidden"><input type="text"  name="stream-<%= id %>-posts" value="40" class="short clearcache"/> posts <span class="space"></span><input type="text" class="short clearcache" name="stream-<%= id %>-days"/> days</dd>\
                        <dt>Number of visible items\
                            <p class="desc">Overall number of items from all connected feeds to show in stream on first load. Button "Show more" will appear if there are more items loaded and cached.</p>\
                        </dt>\
                        <dd><input type="text"  name="stream-<%= id %>-page-posts" value="20" class="short clearcache"/> posts</dd>\
                        <dt class="multiline" style="display:none">Cache\
                            <p class="desc">Caching stream data to reduce loading time</p></dt>\
                        <dd style="display:none">\
                            <label for="stream-<%= id %>-cache"><input id="stream-<%= id %>-cache" class="switcher clearcache" type="checkbox" name="stream-<%= id %>-cache" checked value="yep"/><div><div></div></div></label>\
                        </dd>\
                        <dt class="multiline hidden">Cache lifetime\
                            <p class="desc">Make it longer if feeds are rarely updated or shorter if you need frequent updates.</p></dt>\
                        <dd class="hidden">\
                            <label for="stream-<%= id %>-cache-lifetime"><input id="stream-<%= id %>-cache-lifetime" class="short clearcache" type="text" name="stream-<%= id %>-cache-lifetime" value="10"/> minutes</label>\
                        </dd>\
                        <dt class="multiline">Show lightbox on card click\
                            <p class="desc">Otherwise click will open original URL.</p></dt>\
                        <dd>\
                            <label for="stream-<%= id %>-gallery"><input id="stream-<%= id %>-gallery" class="switcher" type="checkbox" checked name="stream-<%= id %>-gallery" value="yep"/><div><div></div></div></label>\
                        </dd>\
                        <dt class="multiline">Private stream<p class="desc">Show only for logged in users.</p></dt>\
                        <dd>\
                            <label for="stream-<%= id %>-private"><input id="stream-<%= id %>-private" class="switcher" type="checkbox" name="stream-<%= id %>-private" value="yep"/><div><div></div></div></label>\
                        </dd>\
                        <dt class="multiline">Hide stream on a desktop<p class="desc">If you want to create mobiles specific stream only.</p></dt>\
                        <dd>\
                            <label for="stream-<%= id %>-hide-on-desktop"><input id="stream-<%= id %>-hide-on-desktop" class="switcher" type="checkbox" name="stream-<%= id %>-hide-on-desktop" value="yep"/><div><div></div></div></label>\
                        </dd>\
                        <dt class="multiline">Hide stream on a mobile device<p class="desc">If you want to show stream content only on desktops.</p></dt>\
                        <dd>\
                            <label for="stream-<%= id %>-hide-on-mobile"><input id="stream-<%= id %>-hide-on-mobile" class="switcher" type="checkbox" name="stream-<%= id %>-hide-on-mobile" value="yep"/><div><div></div></div></label>\
                        </dd>\
                        <dt class="multiline">Show only media posts<p class="desc">Will filter cached posts and show only that with media.</p></dt>\
                        <dd>\
                            <label for="stream-<%= id %>-show-only-media-posts"><input id="stream-<%= id %>-show-only-media-posts" class="switcher" type="checkbox" name="stream-<%= id %>-show-only-media-posts" value="yep"/><div><div></div></div></label>\
                        </dd>\
                        <dt class="multiline">Titles link<p class="desc">If yes and lightbox is enabled then titles will link to original posts.</p></dt>\
                        <dd>\
                            <label for="stream-<%= id %>-titles"><input id="stream-<%= id %>-titles" class="switcher" type="checkbox" name="stream-<%= id %>-titles" value="yep"/><div><div></div></div></label>\
                        </dd>\
                        <dt class="multiline">Hide meta info<p class="desc">Hide social network icon, name, timestamp in each post.</p></dt>\
                        <dd>\
                            <label for="stream-<%= id %>-hidemeta"><input id="stream-<%= id %>-hidemeta" class="switcher" type="checkbox" name="stream-<%= id %>-hidemeta" value="yep"/><div><div></div></div></label>\
                        </dd>\                        \
                        <dt class="multiline">Hide text<p class="desc">Hide text content of each post.</p></dt>\
                        <dd>\
                            <label for="stream-<%= id %>-hidetext"><input id="stream-<%= id %>-hidetext" class="switcher" type="checkbox" name="stream-<%= id %>-hidetext" value="yep"/><div><div></div></div></label>\
                        </dd>\
                    </dl>\
                    <span id="stream-settings-sbmt-<%= id %>" class="admin-button green-button submit-button">Save Changes</span>\
                </div>\
                                  <%= TV %>\
<div class="section grid-layout-chosen"  data-tab="grid" id="cont-settings-<%= id %>">\
<div class="design-step-1">\
    <h1 class="desc-following">Stream layout</h1>\
    <p class="desc">Choose layout to have different set of size and styling options.</p>\
    <div class="choose-wrapper">\
        <input name="stream-<%= id %>-layout" class="clearcache" id="stream-layout-masonry-<%= id %>" type="radio" value="masonry" checked/><label for="stream-layout-masonry-<%= id %>"><span class="choose-button"><i class="sprite-masonry"></i>Masonry</span><br><span class="desc">This Pinterest-style format will create grid where each card size depends on its content.</span></label>\
        <input name="stream-<%= id %>-layout" class="clearcache" id="stream-layout-grid-<%= id %>" type="radio" value="grid" /><label for="stream-layout-grid-<%= id %>"><span class="choose-button"><i class="sprite-grid"></i>Grid</span><br><span class="desc">Classic grid with cards of same size. Recommended for posts with similar format.</span></label>\
        <input name="stream-<%= id %>-layout" class="clearcache" id="stream-layout-justified-<%= id %>" type="radio" value="justified"/><label for="stream-layout-justified-<%= id %>"><span class="choose-button"><i class="sprite-justified"></i>Justified</span><br><span class="desc">Cards width is justified to have the same height. Only for image posts. Content always overlays.</span></label>\
        </div>\
    </div>\
    <dl class="section-settings settings-masonry">\
<dt class="multiline">Gallery mode\
        <p class="desc">Affects only image posts. Choose if post content overlays post image on mouseover / on touch.</p>\
    </dt>\
    <dd>\
        <label for="stream-<%= id %>-m-overlay"><input id="stream-<%= id %>-m-overlay" class="switcher" type="checkbox" name="stream-<%= id %>-m-overlay" value="yep"/><div><div></div></div></label>\
    </dd>\
                    <dt class="multiline">Responsive settings\
        <p class="desc">Set number of columns you want to show on various screen sizes and space between cards.</p>\
    </dt>\
    <dd class="device-list">\
        <div><i class="flaticon-desktop"></i> <input name="stream-<%= id %>-m-c-desktop" id="stream-<%= id %>-m-c-desktop" type="range" min="1" max="12" step="1" value="5" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-m-s-desktop" name="stream-<%= id %>-m-s-desktop" value="0" class="extra-small"> px spacing</div>\
        <div><i class="flaticon-laptop"></i> <input name="stream-<%= id %>-m-c-laptop" id="stream-<%= id %>-m-c-laptop" type="range" min="1" max="12" step="1" value="4" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-m-s-laptop" name="stream-<%= id %>-m-s-laptop" value="0" class="extra-small"> px spacing</div>\
        <div><i class="flaticon-tablet rotated"></i> <input name="stream-<%= id %>-m-c-tablet-l" id="stream-<%= id %>-m-c-tablet-l" type="range" min="1" max="12" step="1" value="3" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-m-s-tablet-l" name="stream-<%= id %>-m-s-tablet-l" value="0" class="extra-small"> px spacing</div>\
        <div><i class="flaticon-tablet"></i> <input name="stream-<%= id %>-m-c-tablet-p" id="stream-<%= id %>-m-c-tablet-p" type="range" min="1" max="12" step="1" value="2" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-m-s-tablet-p" name="stream-<%= id %>-m-s-tablet-p" value="0" class="extra-small"> px spacing</div>\
        <div><i class="flaticon-phone2 rotated"></i> <input name="stream-<%= id %>-m-c-smart-l" id="stream-<%= id %>-m-c-smart-l" type="range" min="1" max="12" step="1" value="2" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-m-s-smart-l" name="stream-<%= id %>-m-s-smart-l" value="0" class="extra-small"> px spacing</div>\
        <div><i class="flaticon-phone2"></i> <input name="stream-<%= id %>-m-c-smart-p" id="stream-<%= id %>-m-c-smart-p" type="range" min="1" max="12" step="1" value="1" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-m-s-smart-p" name="stream-<%= id %>-m-s-smart-p" value="0" class="extra-small"> px spacing</div>\
    </dd>\
    </dl>\
    <dl class="section-settings settings-grid">\
    <dt class="multiline">Gallery mode\
        <p class="desc">Affects only image posts. Choose if post content overlays post image on mouseover / on touch.</p>\
    </dt>\
    <dd>\
        <label for="stream-<%= id %>-g-overlay"><input id="stream-<%= id %>-g-overlay" class="switcher" type="checkbox" name="stream-<%= id %>-g-overlay" value="yep"/><div><div></div></div></label>\
    </dd>\
         <dt class="multiline">Card Size ratio\
        <p class="desc">Specify the ratio between width and height (X:Y) of card. For non-gallery recommended ratio is 1:2 or 2:3, for gallery is 1:1.</p>\
    </dt>\
    <dd>\
        <input type="text" id="stream-<%= id %>-g-ratio-w" name="stream-<%= id %>-g-ratio-w" value="1" class="extra-small"> : <input type="text" id="stream-<%= id %>-g-ratio-h" name="stream-<%= id %>-g-ratio-h" value="1" class="extra-small"> \
    </dd>\
         <dt class="multiline">Image to card ratio\
        <p class="desc">For non-gallery mode specify image size relative to overall card size.</p>\
    </dt>\
    <dd>\
        <div class="select-wrapper" style="width:150px">\
            <select name="stream-<%= id %>-g-ratio-img" id="stream-<%= id %>-g-ratio-img">\
                <option value="1/2" selected>One half</option>\
                <option value="1/3">One third</option>\
                <option value="2/3">Two thirds</option>\
            </select>\
        </div>\
    </dd>\
     <dt class="multiline">Responsive settings\
        <p class="desc">Set number of columns and space between cards you want to show on various sizes. Keep in mind that size depends on container which can have not full width of screen.</p>\
    </dt>\
    <dd class="device-list">\
        <div><i class="flaticon-desktop"></i> <input name="stream-<%= id %>-c-desktop" id="stream-<%= id %>-c-desktop" type="range" min="1" max="12" step="1" value="5" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-s-desktop" name="stream-<%= id %>-s-desktop" value="0" class="extra-small"> px spacing</div>\
        <div><i class="flaticon-laptop"></i> <input name="stream-<%= id %>-c-laptop" id="stream-<%= id %>-c-laptop" type="range" min="1" max="12" step="1" value="4" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-s-laptop" name="stream-<%= id %>-s-laptop" value="0" class="extra-small"> px spacing</div>\
        <div><i class="flaticon-tablet rotated"></i> <input name="stream-<%= id %>-c-tablet-l" id="stream-<%= id %>-c-tablet-l" type="range" min="1" max="12" step="1" value="3" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-s-tablet-l" name="stream-<%= id %>-s-tablet-l" value="0" class="extra-small"> px spacing</div>\
        <div><i class="flaticon-tablet"></i> <input name="stream-<%= id %>-c-tablet-p" id="stream-<%= id %>-c-tablet-p" type="range" min="1" max="12" step="1" value="2" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-s-tablet-p" name="stream-<%= id %>-s-tablet-p" value="0" class="extra-small"> px spacing</div>\
        <div><i class="flaticon-phone2 rotated"></i> <input name="stream-<%= id %>-c-smart-l" id="stream-<%= id %>-c-smart-l" type="range" min="1" max="12" step="1" value="2" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-s-smart-l" name="stream-<%= id %>-s-smart-l" value="0" class="extra-small"> px spacing</div>\
        <div><i class="flaticon-phone2"></i> <input name="stream-<%= id %>-c-smart-p" id="stream-<%= id %>-c-smart-p" type="range" min="1" max="12" step="1" value="1" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-s-smart-p" name="stream-<%= id %>-s-smart-p" value="0" class="extra-small"> px spacing</div>\
    </dd>\
    </dl>\
<dl class="section-settings settings-justified">\
    <dt class="multiline">Responsive settings\
        <p class="desc">Set height of row you want to show on various screen sizes and space between cards. Please notice that height can\'t always be precise due to algorythm technical details.</p>\
    </dt>\
    <dd class="device-list">\
        <div><i class="flaticon-desktop"></i> Preferred row height is <input type="text" id="stream-<%= id %>-j-h-desktop" name="stream-<%= id %>-j-h-desktop" value="260" class="short"> px with <input type="text" id="stream-<%= id %>-j-s-desktop" name="stream-<%= id %>-j-s-desktop" value="0" class="extra-small"> px spacing</div>\
        <div><i class="flaticon-laptop"></i> Preferred row height is <input type="text" id="stream-<%= id %>-j-h-laptop" name="stream-<%= id %>-j-h-laptop" value="240" class="short"> px with <input type="text" id="stream-<%= id %>-j-s-laptop" name="stream-<%= id %>-j-s-laptop" value="0" class="extra-small"> px spacing</div>\
        <div><i class="flaticon-tablet rotated"></i> Preferred row height is <input type="text" id="stream-<%= id %>-j-h-tablet-l" name="stream-<%= id %>-j-h-tablet-l" value="220" class="short"> px with <input type="text" id="stream-<%= id %>-j-s-tablet-l" name="stream-<%= id %>-j-s-tablet-l" value="0" class="extra-small"> px spacing</div>\
        <div><i class="flaticon-tablet"></i> Preferred row height is <input type="text" id="stream-<%= id %>-j-h-tablet-p" name="stream-<%= id %>-j-h-tablet-p" value="200" class="short"> px with <input type="text" id="stream-<%= id %>-j-s-tablet-p" name="stream-<%= id %>-j-s-tablet-p" value="0" class="extra-small"> px spacing</div>\
        <div><i class="flaticon-phone2 rotated"></i> Preferred row height is <input type="text" id="stream-<%= id %>-j-h-smart-l" name="stream-<%= id %>-j-h-smart-l" value="180" class="short"> px with <input type="text" id="stream-<%= id %>-j-s-smart-l" name="stream-<%= id %>-j-s-smart-l" value="0" class="extra-small"> px spacing</div>\
        <div><i class="flaticon-phone2"></i> Preferred row height is <input type="text" id="stream-<%= id %>-j-h-smart-p" name="stream-<%= id %>-j-h-smart-p" value="160" class="short"> px with <input type="text" id="stream-<%= id %>-j-s-smart-p" name="stream-<%= id %>-j-s-smart-p" value="0" class="extra-small"> px spacing</div>\
    </dd>\
    </dl>\
<div class="button-wrapper"><span id="stream-layout-sbmt-<%= id %>" class="admin-button green-button submit-button" style="margin-bottom:35px">Save Changes</span></div>\
<h1>Grid container settings</h1>\
<dl class="section-settings section-compact">\
    <dt class="multiline">Stream heading\
        <p class="desc">Leave empty to not show.</p></dt>\
    <dd>\
        <input id="stream-<%= id %>-heading" type="text" name="stream-<%= id %>-heading" placeholder="Enter heading"/>\
    </dd>\
    <dt class="multiline">Heading color\
        <p class="desc">Click on input to open colorpicker.</p>\
    </dt>\
    <dd>\
        <input id="heading-color-<%= id %>" data-color-format="rgba" name="stream-<%= id %>-headingcolor" type="text" value="rgb(59, 61, 64)" tabindex="-1">\
        </dd>\
        <dt>Stream subheading</dt>\
        <dd>\
            <input id="stream-<%= id %>-subheading" type="text" name="stream-<%= id %>-subheading" placeholder="Enter subheading"/>\
        </dd>\
        <dt class="multiline">Subheading color\
            <p class="desc">You can also paste color in input.</p>\
        </dt>\
        <dd>\
            <input id="subheading-color-<%= id %>" data-color-format="rgba" name="stream-<%= id %>-subheadingcolor" type="text" value="rgb(114, 112, 114)" tabindex="-1">\
            </dd>\
            <dt><span class="valign">Headings alignment</span></dt>\
            <dd class="">\
                <div class="select-wrapper">\
                    <select name="stream-<%= id %>-hhalign" id="hhalign-<%= id %>">\
                        <option value="center" selected>Centered</option>\
                        <option value="left">Left</option>\
                        <option value="right">Right</option>\
                    </select>\
                </div>\
            </dd>\
            <dt class="multiline">Container background color\
                <p class="desc">You can see it in live preview under Stylings.</p>\
            </dt>\
            <dd>\
                <input data-prop="backgroundColor" id="bg-color-<%= id %>" data-color-format="rgba" name="stream-<%= id %>-bgcolor" type="text" value="rgb(240, 240, 240)" tabindex="-1">\
                </dd>\
                <dt class="multiline">Include filters and search in grid\
                <p class="desc">Toggle filters and search visibility.</p>\
                </dt>\
                <dd>\
                    <label for="stream-<%= id %>-filter"><input id="stream-<%= id %>-filter" class="switcher" type="checkbox" name="stream-<%= id %>-filter" checked value="yep"/><div><div></div></div></label>\
                </dd>\
                <dt>Filters and controls color\
                </dt>\
                <dd>\
                    <input id="filter-color-<%= id %>" data-color-format="rgba" name="stream-<%= id %>-filtercolor" type="text" value="rgb(205, 205, 205)" tabindex="-1">\
                    </dd>\
                    <dt class="multiline">Slider on mobiles <p class="desc">On mobiles grid will turn into slider with 3 items per slide.</p></dt>\
                    <dd>\
                        <label for="stream-<%= id %>-mobileslider"><input id="stream-<%= id %>-mobileslider" class="switcher" type="checkbox" name="stream-<%= id %>-mobileslider" value="yep"/><div><div></div></div></label>\
                    </dd>\
                    <dt class="multiline">Animate grid items <p class="desc">When they appear in viewport (otherwise all items are visible immediately).</p></dt>\
                    <dd>\
                        <label for="stream-<%= id %>-viewportin"><input id="stream-<%= id %>-viewportin" class="switcher" type="checkbox" name="stream-<%= id %>-viewportin" checked value="yep"/><div><div></div></div></label>\
                    </dd>\
                </dl>\
                <span id="stream-cont-sbmt-<%= id %>" class="admin-button green-button submit-button">Save Changes</span>\
            </div>\
            <div class="section"  data-tab="stylings" id="stream-stylings-<%= id %>">\
                <div class="design-step-2 layout-grid">\
                    <h1>Grid cards styling</h1>\
                    <dl class="section-settings section-compact" style="display:none">\
                        <dt><span class="valign">Card dimensions</span></dt>\
                        <dd>Width: <input type="text" data-prop="width" id="width-<%= id %>" name="stream-<%= id %>-width" value="260" class="short clearcache"/> px <span class="space"></span> Margin: <input type="text" id="margin-<%= id %>" value="20" class="short" name="stream-<%= id %>-margin"/> px</dd>\
                        <dt><span class="valign">Card theme</span></dt>\
                        <dd class="theme-choice">\
                            <input id="theme-classic-<%= id %>" type="radio" class="clearcache" name="stream-<%= id %>-theme" checked value="classic"/> <label for="theme-classic-<%= id %>">Classic</label> <input class="clearcache" id="theme-flat-<%= id %>" type="radio" name="stream-<%= id %>-theme" value="flat"/> <label for="theme-flat-<%= id %>">Modern</label>\
                        </dd>\
                    </dl>\
<dl class="classic-style style-choice section-settings section-compact" style="display:block">\
    <dt class="hide"><span class="valign">Info style</span></dt>\
    <dd class="hide">\
        <div class="select-wrapper">\
            <select name="stream-<%= id %>-gc-style" id="gc-style-<%= id %>">\
                <option value="style-1" selected>Centered meta, round icon</option>\
                <option value="style-2">Centered meta, bubble icon</option>\
                <option value="style-6">Centered meta, no social icon</option>\
                <option value="style-3">Userpic, rounded icon</option>\
                <option value="style-4">No userpic, rounded icon</option>\
                <option value="style-5">No userpic, bubble icon</option>\
            </select>\
        </div>\
    </dd>\
    <dt><span class="valign">Author picture position & size</span></dt>\
    <dd>\
        <div class="select-wrapper">\
            <select name="stream-<%= id %>-upic-pos" id="stream-<%= id %>-upic-pos">\
                <option value="timestamp" selected>With timestamp</option>\
                <option value="centered">Centered</option>\
                <option value="centered-big">Big Centered & Overlaps Image</option>\
                <option value="off">Don\'t show it</option>\
            </select>\
        </div>\
    </dd>\
    <dt><span class="valign">Card corners style</span></dt>\
    <dd>\
        <div class="select-wrapper">\
            <select name="stream-<%= id %>-upic-style" id="stream-<%= id %>-upic-style">\
                <option value="round" selected>Rounded</option>\
                <option value="square">Plain</option>\
            </select>\
        </div>\
    </dd>\
    <dt><span class="valign">Social icon style</span></dt>\
    <dd>\
        <div class="select-wrapper">\
            <select name="stream-<%= id %>-icon-style" id="stream-<%= id %>-icon-style">\
                <option value="label1" selected>Label</option>\
                <option value="label2">Corner icon</option>\
                <option value="stamp1">Timestamp</option>\
                <option value="off">Off</option>\
            </select>\
        </div>\
    </dd>\
    <dt class="multiline">Card background color\
        <p class="desc">Click on field to open colorpicker.</p>\
    </dt>\
    <dd>\
        <input data-prop="backgroundColor" id="card-color-<%= id %>" data-color-format="rgba" name="stream-<%= id %>-cardcolor" type="text" value="rgb(255,255,255)" tabindex="-1">\
        </dd>\
        <dt class="multiline">Color for heading & name\
            <p class="desc">Also for social buttons hover.</p>\
        </dt>\
        <dd>\
            <input data-prop="color" id="name-color-<%= id %>" data-color-format="rgba" name="stream-<%= id %>-namecolor" type="text" value="rgb(59, 61, 64)" tabindex="-1">\
            </dd>\
            <dt>Regular text color\
            </dt>\
            <dd>\
                <input data-prop="color" id="text-color-<%= id %>" data-color-format="rgba" name="stream-<%= id %>-textcolor" type="text" value="rgb(131, 141, 143)" tabindex="-1">\
                </dd>\
                <dt>Links color</dt>\
                <dd>\
                    <input data-prop="color" id="links-color-<%= id %>" data-color-format="rgba" name="stream-<%= id %>-linkscolor" type="text" value="rgb(94, 159, 202)" tabindex="-1">\
                    </dd>\
                    <dt class="multiline">Other text color\
                        <p class="desc">Nicknames, timestamps.</p></dt>\
                    <dd>\
                        <input data-prop="color" id="other-color-<%= id %>" data-color-format="rgba" name="stream-<%= id %>-restcolor" type="text" value="rgb(132, 118, 129)" tabindex="-1">\
                        </dd>\
                        <dt>Card shadow</dt>\
                        <dd>\
                            <input data-prop="box-shadow" id="shadow-color-<%= id %>" data-color-format="rgba" name="stream-<%= id %>-shadow" type="text" value="rgba(0,0,0,.05)" tabindex="-1">\
                            </dd>\
                            <dt>Overlay for gallery cards</dt>\
                            <dd>\
                                <input data-prop="border-color" id="bcolor-<%= id %>" data-color-format="rgba" name="stream-<%= id %>-bcolor" type="text" value="rgba(0, 0, 0, 0.75)" tabindex="-1">\
                                </dd>\
                                <dt><span class="valign">Text alignment</span></dt>\
                                <dd class="">\
                                    <div class="select-wrapper">\
                                        <select name="stream-<%= id %>-talign" id="talign-<%= id %>">\
                                            <option value="left" selected>Left</option>\
                                            <option value="center">Centered</option>\
                                            <option value="right">Right</option>\
                                        </select>\
                                    </div>\
                                </dd>\
                                <dt><span class="valign">Icons look & feel</span></dt>\
                                <dd class="">\
                                    <div class="select-wrapper">\
                                        <select name="stream-<%= id %>-icons-style" id="icons-style-<%= id %>">\
                                            <option value="outline" selected>Outlined</option>\
                                            <option value="fill">Solid</option>\
                                        </select>\
                                    </div>\
                                </dd>\
                                <dt class="hide">Preview</dt>\
                                <dd class="preview">\
                                    <h1>Card builder - drag\'n\'drop</h1>\
                                    <input type="hidden" id="stream-<%= id %>-template" name="stream-<%= id %>-template"/>\
                                    <div data-preview="bg-color" class="ff-stream-wrapper ff-layout-grid ff-theme-classic ff-layout-masonry ff-upic-timestamp ff-upic-round ff-align-left ff-sc-label1 shuffle">\
                                        <div data-preview="width" class="ff-item ff-instagram shuffle-item filtered" style="visibility: visible; opacity:1;">\
                                            <div data-preview="card-color,shadow-color" class="picture-item__inner picture-item__inner--transition">\
                                                <div class="ff-item-cont">\
                                                    <span data-template="image" class="ff-img-holder ff-item__draggable"><img src="<%= plugin_url %>/assets/alex_strohl.jpg" style="width:100%;"></span>\
                                                    <h4 data-template="header" data-preview="name-color" class="ff-item__draggable">Header example</h4>\
                                                    <div data-template="text" data-preview="text-color" class="ff-content ff-item__draggable">This is regular text paragraph, can be tweet, facebook post etc. This is example of <a href="#" data-preview="links-color">link in text</a>.</div>\
                                                    <h6 class="ff-label-wrapper"><i class="ff-icon"><i class="ff-icon-inner"><span class="ff-label-text">instagram</span></i></i></h6>\
                                                    <div data-template="meta" class="ff-item-meta ff-item__draggable">\
                                                        <span class="ff-userpic" style="background:url(<%= plugin_url %>/assets/alex_strohl_user.jpg)"><i data-preview="border-color" class="ff-icon"><i class="ff-icon-inner"></i></i></span><h6><a data-preview="name-color" target="_blank" rel="nofollow" href="#" class="ff-name">Alex Strohl</a></h6><a data-preview="other-color" target="_blank" rel="nofollow" href="#" class="ff-nickname">@alex_strohl</a><a data-preview="other-color" target="_blank" rel="nofollow" href="#" class="ff-timestamp">21m ago </a>\
                                                    </div>\
                                                    <h6 class="ff-item-bar"><a data-preview="other-color" href="#" class="ff-likes" target="_blank"><i class="ff-icon-like"></i> <span>89K</span></a><a data-preview="other-color" href="#" class="ff-comments" target="_blank"><i class="ff-icon-comment"></i> <span>994</span></a><div class="ff-share-wrapper"><i data-preview="other-color" class="ff-icon-share"></i><div class="ff-share-popup"><a href="http://www.facebook.com/sharer.php?u=https%3A%2F%2Fwww.instagram.com%2Fp%2FBLAaLZjBRg8%2F" class="ff-fb-share" target="_blank"><span>Facebook</span></a><a href="https://twitter.com/share?url=https%3A%2F%2Fwww.instagram.com%2Fp%2FBLAaLZjBRg8%2F" class="ff-tw-share" target="_blank"><span>Twitter</span></a><a href="https://plus.google.com/share?url=https%3A%2F%2Fwww.instagram.com%2Fp%2FBLAaLZjBRg8%2F" class="ff-gp-share" target="_blank"><span>Google+</span></a><a href="https://www.pinterest.com/pin/create/button/?url=https%3A%2F%2Fwww.instagram.com%2Fp%2FBLAaLZjBRg8%2F&amp;media=https%3A%2F%2Fscontent.cdninstagram.com%2Ft51.2885-15%2Fsh0.08%2Fe35%2Fp640x640%2F14482046_188451531582331_7449129988999086080_n.jpg%3Fig_cache_key%3DMTM1MTE5NTAyMDc2NTc2MzY0NA%253D%253D.2" class="ff-pin-share" target="_blank"><span>Pinterest</span></a></div></div></h6>\
                                                </div>\
                                            </div>\
                                        </div>\
                                    </div>\
                                </dd>\
                                </dl>\
<span id="stream-stylings-sbmt-<%= id %>" class="admin-button green-button submit-button">Save Changes</span>\
</div>\
</div>\
<div class="section" data-tab="css" id="css-<%= id %>">\
                <h1 class="desc-following">Stream custom CSS</h1>\
                <p class="desc" style="margin-bottom:10px">\
                Prefix your selectors with <strong>#ff-stream-<%= id %></strong> to target this specific stream.\
                </p>\
                <textarea  name="stream-<%= id %>-css" cols="100" rows="10" id="stream-<%= id %>-css"/> </textarea>\
            <p style="margin-top:10px"><span id="stream-css-sbmt-<%= id %>" class="admin-button green-button submit-button">Save Changes</span><p>\
            </div>\
            <div class="section shortcode-section" data-tab="shortcode" id="shortcode-<%= id %>">\
                <h1 class="desc-following">Stream shortcode</h1>\
                <p class="desc" style="margin-bottom:10px">\
                Place this shortcode anywhere on your site.\
                </p>\
                <p class="shortcode"><span>[ff id=\"<%= id %>\"]</span></p>\
            </div>\
            <div class="section footer">\
<div class="width-wrapper"><div class="ff-table"><div class="ff-cell">\
    Flow-Flow Social Stream plugin<br>\
    Version: <%= version %><br>\
    Made by <a href="http://looks-awesome.com/">Looks Awesome</a>\
</div>\
<div class="ff-cell">\
    <h1>HOT TOPICS</h1>\
    <a target="_blank" href="http://docs.social-streams.com/article/42-first-steps-flow-wp">First Steps With Plugin</a><br>\
    <a target="_blank" href="http://docs.social-streams.com/article/46-authenticate-with-facebook">Connect Facebook</a><br>\
    <a target="_blank" href="http://docs.social-streams.com/article/56-issues-using-big-number-of-feeds">Issues With Streams</a><br>\
    <a target="_blank" href="http://docs.social-streams.com/collection/104-faq">Frequently Asked Questions</a>\
</div>\
<div class="ff-cell">\
    <h1>USEFUL LINKS</h1>\
<a href="http://looks-awesome.com/help">Help Center</a><br>\
<a href="http://looks-awesome.com/">Looks Awesome Site</a><br>\
<a href="https://twitter.com/looks_awesooome">LA Twitter</a><br>\
    <a href="https://www.facebook.com/looksawesooome">LA Facebook</a>\
    </div>\
    </div>\
    </div>\
    </div>',
    twitterView:    '\
<div class="feed-view" data-feed-type="twitter" data-uid="<%= uid %>">\
<h1>Content settings for Twitter feed</h1>\
<dl class="section-settings">\
<dt>Timeline type </dt>\
<dd>\
<input id="<%= uid %>-home-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="home_timeline" checked/>\
<label for="<%= uid %>-home-timeline-type">Home timeline</label><br><br>\
<input id="<%= uid %>-user-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="user_timeline" />\
<label for="<%= uid %>-user-timeline-type">User timeline</label><br><br>\
<input id="<%= uid %>-search-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="search"/>\
<label for="<%= uid %>-search-timeline-type">Tweets by search</label><br><br>\
<input id="<%= uid %>-list-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="list_timeline"/>\
<label for="<%= uid %>-list-timeline-type">User list</label><br><br>\
<input id="<%= uid %>-fav-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="favorites"/>\
<label for="<%= uid %>-fav-timeline-type">User favorites</label><br><br>\
</dd>\
<dt>Content to show</dt>\
<dd><input type="text" name="<%= uid %>-content" placeholder="What content to stream"/>\
<p class="desc">1. For home timeline enter you own nickname (without @)<br>\
2. For user timeline enter nickname (without @) of any public Twitter<br>\
3. For search enter any word or #hashtag (look <a href="https://dev.twitter.com/rest/public/search">here</a> for advanced terms)<br>\
4. For user list enter username here and list name in field below<br>\
5. For user favorites enter username\
</p>\
</dd>\
<dt>List name</dt>\
<dd>\
<input type="text" name="<%= uid %>-list-name" placeholder=""/>\
<p class="desc">Required if you choose list feed.</p>\
</dd>\
<dt class="">Tweets language</dt>\
<dd>\
<div class="select-wrapper">\
<select id="<%= uid %>-lang" name="<%= uid %>-lang">\
<option value="all" selected>Any Language</option>\
<option value="am">Amharic (አማርኛ)</option>\
<option value="ar">Arabic (العربية)</option>\
<option value="bg">Bulgarian (Български)</option>\
<option value="bn">Bengali (বাংলা)</option>\
<option value="bo">Tibetan (བོད་སྐད)</option>\
<option value="chr">Cherokee (ᏣᎳᎩ)</option>\
<option value="da">Danish (Dansk)</option>\
<option value="de">German (Deutsch)</option>\
<option value="dv">Maldivian (ދިވެހި)</option>\
<option value="el">Greek (Ελληνικά)</option>\
<option value="en">English (English)</option>\
<option value="es">Spanish (Español)</option>\
<option value="fa">Persian (فارسی)</option>\
<option value="fi">Finnish (Suomi)</option>\
<option value="fr">French (Français)</option>\
<option value="gu">Gujarati (ગુજરાતી)</option>\
<option value="iw">Hebrew (עברית)</option>\
<option value="hi">Hindi (हिंदी)</option>\
<option value="hu">Hungarian (Magyar)</option>\
<option value="hy">Armenian (Հայերեն)</option>\
<option value="in">Indonesian (Bahasa Indonesia)</option>\
<option value="is">Icelandic (Íslenska)</option>\
<option value="it">Italian (Italiano)</option>\
<option value="iu">Inuktitut (ᐃᓄᒃᑎᑐᑦ)</option>\
<option value="ja">Japanese (日本語)</option>\
<option value="ka">Georgian (ქართული)</option>\
<option value="km">Khmer (ខ្មែរ)</option>\
<option value="kn">Kannada (ಕನ್ನಡ)</option>\
<option value="ko">Korean (한국어)</option>\
<option value="lo">Lao (ລາວ)</option>\
<option value="lt">Lithuanian (Lietuvių)</option>\
<option value="ml">Malayalam (മലയാളം)</option>\
<option value="my">Myanmar (မြန်မာဘာသာ)</option>\
<option value="ne">Nepali (नेपाली)</option>\
<option value="nl">Dutch (Nederlands)</option>\
<option value="no">Norwegian (Norsk)</option>\
<option value="or">Oriya (ଓଡ଼ିଆ)</option>\
<option value="pa">Panjabi (ਪੰਜਾਬੀ)</option>\
<option value="pl">Polish (Polski)</option>\
<option value="pt">Portuguese (Português)</option>\
<option value="ru">Russian (Русский)</option>\
<option value="si">Sinhala (සිංහල)</option>\
<option value="sv">Swedish (Svenska)</option>\
<option value="ta">Tamil (தமிழ்)</option>\
<option value="te">Telugu (తెలుగు)</option>\
<option value="th">Thai (ไทย)</option>\
<option value="tl">Tagalog (Tagalog)</option>\
<option value="tr">Turkish (Türkçe)</option>\
<option value="ur">Urdu (ﺍﺭﺩﻭ)</option>\
<option value="vi">Vietnamese (Tiếng Việt)</option>\
<option value="zh">Chinese (中文)</option>\
</select>\
</div>\
<p class="desc">As detected by Twitter. Only for search feeds.</p>\
</dd>\
<!--\
<dt class="multiline">Geolocalization<p class="desc">Only for search</p></dt>\
<dd>\
<label for="<%= uid %>-use-geo"><input id="<%= uid %>-use-geo" class="switcher" type="checkbox" name="<%= uid %>-use-geo" value="yep"/><div><div></div></div></label>\
<div id="<%= uid %>-geo-container" style="width: 500px; height: 400px; display: none;"></div>\
<input type="hidden" id="<%= uid %>-latitude" name="<%= uid %>-latitude" value=""/>\
<input type="hidden" id="<%= uid %>-longitude" name="<%= uid %>-longitude" value=""/>\
<input type="text" id="<%= uid %>-radius" name="<%= uid %>-radius" placeholder="Enter radius (in meter)" style="display: none;"/>\
</dd>-->\
<dt>Include retweets (if present)</dt>\
<dd>\
<label for="<%= uid %>-retweets"><input id="<%= uid %>-retweets" class="switcher" type="checkbox" name="<%= uid %>-retweets" value="yep"/><div><div></div></div></label>\
</dd>\
<dt>Include replies (if present)</dt>\
<dd>\
<label for="<%= uid %>-replies"><input id="<%= uid %>-replies" class="switcher" type="checkbox" name="<%= uid %>-replies" value="yep"/><div><div></div></div></label>\
</dd>\
<dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="5">Every 5 min</option> <option value="30" selected>Every 30 min</option> <option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>Posts to load during update</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>Moderation enabled</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
</dl>\
<input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
',
    facebookView:   '\
<div class="feed-view"  data-feed-type="facebook" data-uid="<%= uid %>">\
<h1>Content settings for Facebook feed</h1>\
<dl class="section-settings">\
<dt>Timeline type </dt>\
<dd>\
<input id="<%= uid %>-page-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="page_timeline" checked />\
<label for="<%= uid %>-page-timeline-type">Facebook public page</label><br><br>\
<input id="<%= uid %>-group-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="group" />\
<label for="<%= uid %>-group-timeline-type">Public group page</label><br><br>\
<input id="<%= uid %>-album-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="album" />\
<label for="<%= uid %>-album-timeline-type">Public album</label>\
</dd>\
<dt>\
Content to show\
</dt>\
<dd><input type="text" name="<%= uid %>-content" placeholder="What content to stream"/>\
<p class="desc">\
1. For public Page posts enter nickname of any public page (or ID if it is in page address)<br>\
2. For public Group enter its ID (use <a target="_blank" href="http://lookup-id.com/">this tool</a>)<br>\
3. For public Album enter its ID. <a target="_blank" href="http://docs.social-streams.com/article/50-find-facebook-album-id">Where to find?</a>\
</p></dd>\
<dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="5">Every 5 min</option> <option value="30" selected>Every 30 min</option> <option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>Posts to load during update</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>Moderation enabled</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
</dl>\
<input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
                    </div>\
',
    vimeoView:      '\
<div class="feed-view"  data-feed-type="vimeo" data-uid="<%= uid %>">\
<h1>Content settings for Vimeo feed</h1>\
<dl class="section-settings">\
<dt>Feed type </dt>\
<dd>\
<input id="<%= uid %>-type-videos" type="radio" name="<%= uid %>-timeline-type" value="videos" checked/>\
<label for="<%= uid %>-type-videos">Own videos</label><br><br>\
<input id="<%= uid %>-type-likes" type="radio" name="<%= uid %>-timeline-type" value="likes" />\
<label for="<%= uid %>-type-likes">Liked videos</label><br><br>\
<input id="<%= uid %>-type-channel" type="radio" name="<%= uid %>-timeline-type" value="channel" />\
<label for="<%= uid %>-type-channel">Channel videos</label><br><br>\
<input id="<%= uid %>-type-group" type="radio" name="<%= uid %>-timeline-type" value="group" />\
<label for="<%= uid %>-type-group">Group videos</label><br><br>\
<input id="<%= uid %>-type-album" type="radio" name="<%= uid %>-timeline-type" value="album" />\
<label for="<%= uid %>-type-album">Album videos</label>\
</dd>\
<dt>\
Content to show\
</dt>\
<dd>\
<input type="text" name="<%= uid %>-content" placeholder="What content to stream"/>\
<p class="desc">Enter nickname of Vimeo user/album/channel (only users have <strong>liked</strong> videos feed).</p>\
</dd>\
<dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="5">Every 5 min</option> <option value="30" selected>Every 30 min</option> <option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>Posts to load during update</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>Moderation enabled</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
</dl>\
<input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
',
    googleView:     '\
<div class="feed-view" data-feed-type="google" data-uid="<%= uid %>">\
  <h1>Content settings for Google+ feed</h1>\
  <dl class="section-settings">\
      <dt>\
      Content to show\
      </dt>\
      <dd><input type="text" name="<%= uid %>-content" placeholder="+UserName"/>\
          <p class="desc">Google username starting with plus or numeric ID of your page.</p>\
      </dd>\
      <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="5">Every 5 min</option> <option value="30" selected>Every 30 min</option> <option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>Posts to load during update</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>Moderation enabled</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
  </dl>\
  <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
',
    rssView:        '\
  <div class="feed-view"  data-feed-type="rss" data-uid="<%= uid %>">\
      <h1>Content settings for RSS feed</h1>\
      <dl class="section-settings">\
          <dt class="">Content to show</dt>\
          <dd class=""><input type="text" name="<%= uid %>-content" placeholder="Enter RSS feed full URL"/></dd>\
          <dt class="multiline">RSS channel name<p class="desc">Fill if RSS does not have own title.</p></dt><dd><input type="text" name="<%= uid %>-channel-name" placeholder="Enter name to show in card"/></dd>\
          <dt>Avatar url</dt>\
          <dd>\
              <input type="text" name="<%= uid %>-avatar-url" placeholder="Enter avatar full URL"/>\
          </dd>\
          <dt>Hide caption</dt>\
          <dd>\
              <label for="<%= uid %>-hide-caption"><input id="<%= uid %>-hide-caption" class="switcher" type="checkbox" name="<%= uid %>-hide-caption" value="yep"/><div><div></div></div></label>\
          </dd>\
          <dt>Rich text</dt>\
          <dd>\
              <label for="<%= uid %>-rich-text"><input id="<%= uid %>-rich-text" class="switcher" type="checkbox" name="<%= uid %>-rich-text" value="yep"/><div><div></div></div></label>\
          </dd>\
          <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="5">Every 5 min</option> <option value="30" selected>Every 30 min</option> <option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>Posts to load during update</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>Moderation enabled</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
      </dl>\
      <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
  </div>\
',
    pinterestView:  '\
  <div class="feed-view" data-feed-type="pinterest" data-uid="<%= uid %>">\
      <h1>Content settings for Pinterest feed</h1>\
      <dl class="section-settings">\
          <dt class="">Content to show</dt>\
          <dd class=""><input type="text" name="<%= uid %>-content" placeholder="What content to stream"/>\
              <p class="desc">e.g. <strong>elainen</strong> (for user feed) or <strong>elainen/cute-animals</strong> (for user board).\
              </p></dd>\
              <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="5">Every 5 min</option> <option value="30" selected>Every 30 min</option> <option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>Posts to load during update</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>Moderation enabled</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
      </dl>\
      <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
  </div>\
                      ',
    instagramView:  '\
  <div class="feed-view" data-feed-type="instagram" data-uid="<%= uid %>">\
      <h1>Content settings for Instagram feed</h1>\
      <dl class="section-settings">\
          <dt>Timeline type</dt>\
          <dd>\
          <input id="<%= uid %>-user-timeline-type" checked type="radio" name="<%= uid %>-timeline-type" value="user_timeline"/>\
            <label for="<%= uid %>-user-timeline-type">User</label><br><br>\
          <input id="<%= uid %>-likes-type"  type="radio" name="<%= uid %>-timeline-type" value="likes"/>\
            <label for="<%= uid %>-likes-type">Likes timeline</label><br><br>\
          <input id="<%= uid %>-search-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="tag"/>\
            <label for="<%= uid %>-search-timeline-type">Hashtag</label><br><br>\
          <input id="<%= uid %>-location-type" type="radio" name="<%= uid %>-timeline-type" value="location"/>\
            <label for="<%= uid %>-location-type">Location</label>\
          </dt>\
          <dt>Content to show</dt>\
          <dd>\
              <input type="text" name="<%= uid %>-content" placeholder="What content to stream"/>\
              <p class="desc">\
                    1. For user timeline enter nickname of any public Instagram account<br>\
                    2. For likes timeline enter you own nickname<br>\
                    3. For photos by hashtag enter one word without #<br>\
                    4. For photos by location enter location id\
              </p>\
                      </dd>\
                      <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="5">Every 5 min</option> <option value="30" selected>Every 30 min</option> <option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>Posts to load during update</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>Moderation enabled</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
  </dl>\
  <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
                                                      ',
    wordpressView:  '\
<div class="feed-view" data-feed-type="wordpress" data-uid="<%= uid %>">\
  <h1>Content settings for WordPress feed</h1>\
  <dl class="section-settings">\
      <dt>Show latest</dt>\
      <dd>\
          <input id="<%= uid %>-wordpress-posts" type="radio" name="<%= uid %>-wordpress-type" checked value="posts"/> <label for="<%= uid %>-wordpress-posts">Posts</label>\
          <input id="<%= uid %>-wordpress-comments" type="radio" name="<%= uid %>-wordpress-type" value="comments"/> <label for="<%= uid %>-wordpress-comments">Comments</label>\
      </dd>\
      <dt>Category Name</dt>\
      <dd>\
          <input type="text" name="<%= uid %>-category-name" placeholder="Category name"/>\
          <p class="desc">If you choose Posts then you can show specific category,<br> enter category name or names divided by comma or leave empty to show all.</p>\
          </dd>\
          <dt>Post ID</dt>\
          <dd>\
              <input type="text" name="<%= uid %>-post-id" placeholder="Post ID"/>\
              <p class="desc">If you choose Comments then you can show specific post comments,<br> enter post ID.</p>\
              </dd>\
              <dt>Custom post slug</dt>\
              <dd>\
                  <input type="text" name="<%= uid %>-slug" placeholder="Custom post slug"/>\
                  <p class="desc">If you want to show only custom posts of specific type.</p>\
              </dd>\
              <dt>Shortcodes in post</dt>\
              <dd>\
              <input id = "<%= uid %>-strip" type = "radio" name = "<%= uid %>-shortcodes" checked value = "strip" /> <label for="<%= uid %>-strip">Remove shortcodes</label>\
              <input id="<%= uid %>-expand" type="radio" name="<%= uid %>-shortcodes" value="expand"/> <label for="<%= uid %>-expand">Expand shortcodes</label> <br>\
                  <p class="desc" style="margin-top: 5px">Disclaimer: we do not guarantee compatibility with any shortcodes if you choose expanding option</p>\
              </dd>\
              <dt>Include post title in comments</dt>\
              <dd>\
                  <label for="<%= uid %>-include-post-title">\
                      <input id="<%= uid %>-include-post-title" class="switcher" type="checkbox" name="<%= uid %>-include-post-title" value="yep"/> <div><div></div></div>\
                  </label>\
              </dd>\
              <dt>Use excerpt instead of text</dt>\
              <dd>\
                  <label for="<%= uid %>-use-excerpt">\
                      <input id="<%= uid %>-use-excerpt" class="switcher" type="checkbox" name="<%= uid %>-use-excerpt" value="yep"/> <div><div></div></div>\
                  </label>\
              </dd>\
              <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="5">Every 5 min</option> <option value="30" selected>Every 30 min</option> <option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>Posts to load during update</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>Moderation enabled</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
</dl>\
<input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
              ',
    youtubeView:    '\
  <div class="feed-view" data-feed-type="youtube" data-uid="<%= uid %>">\
      <h1>Content settings for YouTube feed</h1>\
      <dl class="section-settings">\
          <dt>Timeline type</dt>\
          <dd>\
              <input id="<%= uid %>-user-timeline-type"  type="radio" name="<%= uid %>-timeline-type" value="user_timeline" checked/>\
              <label for="<%= uid %>-user-timeline-type">User</label><br><br>\
              <input id="<%= uid %>-channel-type"  type="radio" name="<%= uid %>-timeline-type" value="channel"/>\
              <label for="<%= uid %>-channel-type">Channel</label><br><br>\
                  <input id="<%= uid %>-pl-type"  type="radio" name="<%= uid %>-timeline-type" value="playlist"/>\
                  <label for="<%= uid %>-pl-type">Playlist</label><br><br>\
                      <input id="<%= uid %>-search-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="search"/>\
                      <label for="<%= uid %>-search-timeline-type">Search</label>\
                  </dt>\
                      <dt class="">Content to show</dt>\
                      <dd class=""><input type="text" name="<%= uid %>-content" placeholder="What content to stream"/>\
                          <p class="desc">\
                          1. For user feed enter YouTube username with public access eg. <strong>VEVO</strong><br>\
                          2. For channel enter channel ID<br>\
                              3. For playlist enter playlist ID<br>\
                                  4. For search enter any word</p>\
                              </dd>\
                              <dt>Playlist reverse order</dt>\
      <dd>\
          <label for="<%= uid %>-playlist-order">\
              <input id="<%= uid %>-playlist-order" class="switcher" type="checkbox" name="<%= uid %>-playlist-order" value="yep"/> <div><div></div></div>\
          </label>\
      </dd>\
      <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="5">Every 5 min</option> <option value="30" selected>Every 30 min</option> <option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>Posts to load during update</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>Moderation enabled</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
</dl>\
<input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
      ',
    vineView:       '\
<div class="feed-view" data-feed-type="vine" data-uid="<%= uid %>">\
<h1>Content settings for Vine feed</h1>\
<dl class="section-settings">\
 <dt>Timeline type</dt>\
 <dd>\
     <input id="<%= uid %>-user-timeline-type"  type="radio" name="<%= uid %>-timeline-type" value="user_timeline" checked/>\
     <label for="<%= uid %>-user-timeline-type">User</label><br><br>\
     <input id="<%= uid %>-popular-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="liked"/>\
     <label for="<%= uid %>-popular-timeline-type">User likes</label><br><br>\
         <input id="<%= uid %>-search-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="tag"/>\
         <label for="<%= uid %>-search-timeline-type">Hashtag</label>\
     </dt>\
         <dt class="">Content to show</dt>\
         <dd class="">\
             <input type="text" name="<%= uid %>-content" placeholder="What content to stream"/>\
             <p class="desc">\
             1. For user timeline enter Vine account username or ID <a target="_blank" href="http://docs.social-streams.com/article/52-find-vine-id">See instructions</a><br>\
             2. For liked timeline enter Vine account username or ID <a target="_blank" href="http://docs.social-streams.com/article/52-find-vine-id">See instructions</a><br>\
                 3. To stream posts by hashtag enter one word without #\
                 </p>\
             </dd>\
             <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="5">Every 5 min</option> <option value="30" selected>Every 30 min</option> <option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>Posts to load during update</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>Moderation enabled</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
 </dl>\
 <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
     ',
    dribbbleView:   '\
     <div class="feed-view" data-feed-type="dribbble" data-uid="<%= uid %>">\
         <h1>Content settings for Dribbble feed</h1>\
     <dl class="section-settings">\
         <dt>Timeline type</dt>\
         <dd>\
             <input id="<%= uid %>-user-timeline-type"  type="radio" name="<%= uid %>-timeline-type" value="user_timeline" checked/>\
             <label for="<%= uid %>-user-timeline-type">User</label><br><br>\
             <input id="<%= uid %>-popular-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="liked"/>\
             <label for="<%= uid %>-popular-timeline-type">Liked by user</label><br><br>\
             </dt>\
                 <dt class="">Content to show</dt>\
                 <dd class=""><input type="text" name="<%= uid %>-content" placeholder="What content to stream"/>\
                     <p class="desc">Enter Dribbble username.</p>\
                 </dd>\
                 <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="5">Every 5 min</option> <option value="30" selected>Every 30 min</option> <option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>Posts to load during update</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>Moderation enabled</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
 </dl>\
 <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
                                                                                                                 ',
    foursquareView: '\
     <div class="feed-view" data-feed-type="foursquare" data-uid="<%= uid %>">\
         <h1>Content settings for Foursquare feed</h1>\
         <dl class="section-settings">\
             <dt class="">Content to show</dt>\
             <dd class=""><input type="text" name="<%= uid %>-content" placeholder="What content to stream"/>\
                 <p class="desc">Enter venue ID (<a target="_blank" href="http://docs.social-streams.com/article/116-find-foursquare-location-id">Find it in location URL</a> ).</p>\
             </dd>\
             <dt>Content type</dt>\
             <dd>\
                 <input id="<%= uid %>-foursquare-tips" type="radio" name="<%= uid %>-content-type" value="tips" checked/> <label for="<%= uid %>-foursquare-tips">Tips</label>\
                 <input id="<%= uid %>-foursquare-photos" type="radio" name="<%= uid %>-content-type" value="photos"/> <label for="<%= uid %>-foursquare-photos">Photos</label>\
             </dd>\
             <dt>Only text</dt>\
             <dd>\
                 <label for="<%= uid %>-only-text"><input id="<%= uid %>-only-text" class="switcher" type="checkbox" name="<%= uid %>-only-text" value="yep"/><div><div></div></div></label>\
             </dd>\
             <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="5">Every 5 min</option> <option value="30" selected>Every 30 min</option> <option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>Posts to load during update</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>Moderation enabled</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
 </dl>\
 <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
                                                                                                                 ',
    flickrView:     '\
     <div class="feed-view" data-feed-type="flickr" data-uid="<%= uid %>">\
         <h1>Content settings for Flickr feed</h1>\
         <dl class="section-settings">\
             <dt>Timeline type</dt>\
             <dd>\
                 <input id="<%= uid %>-user_timeline" type="radio" checked name="<%= uid %>-timeline-type" value="user_timeline"/>\
                 <label for="<%= uid %>-user_timeline">User timeline</label><br><br>\
                 <input id="<%= uid %>-tag" type="radio" name="<%= uid %>-timeline-type" value="tag"/>\
                 <label for="<%= uid %>-tag">Tag</label>\
             </dt>\
                 <dt class="">Content to show</dt>\
                 <dd class=""><input type="text" name="<%= uid %>-content" placeholder="What content to stream"/>\
                     <p class="desc">1. For user timeline enter Flickr username<br>2. For tag enter word or words divided by comma</p>\
                     </dd>\
                     <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="5">Every 5 min</option> <option value="30" selected>Every 30 min</option> <option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>Posts to load during update</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>Moderation enabled</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
     </dl>\
     <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
 </div>\
                                                                                                                             ',
tumblrView:     '\
     <div class="feed-view" data-feed-type="tumblr" data-uid="<%= uid %>">\
         <h1>Content settings for Tumblr feed</h1>\
         <dl class="section-settings">\
             <dt class="">Content to show</dt>\
             <dd class=""><input type="text" name="<%= uid %>-content" placeholder="What content to stream"/>\
                 <p class="desc">Enter Tumblr username to show images from tlog.</p>\
             </dd>\
             <dt>Rich text</dt>\
             <dd>\
                 <label for="<%= uid %>-rich-text"><input id="<%= uid %>-rich-text" class="switcher" type="checkbox" name="<%= uid %>-rich-text" value="yep"/><div><div></div></div></label>\
             </dd>\
             <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="5">Every 5 min</option> <option value="30" selected>Every 30 min</option> <option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>Posts to load during update</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>Moderation enabled</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
 </dl>\
 <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
 ',
linkedinView:   '\
     <div class="feed-view" data-feed-type="linkedin" data-uid="<%= uid %>">\
         <h1>Content settings for LinkedIn feed</h1>\
         <dl class="section-settings">\
             <dt class="">Content to show</dt>\
             <dd class=""><input type="text" name="<%= uid %>-content" placeholder="What content to stream"/>\
                 <p class="desc">Enter company ID, <a href="http://docs.social-streams.com/article/51-find-linkedin-id" target="_blank">find out where to get</a> if it is not in company page URL.</p>\
             </dd>\
             <dt>Event type</dt>\
             <dd>\
                 <input id="<%= uid %>-status-update" type="radio" name="<%= uid %>-event-type" value="status-update"/> <label for="<%= uid %>-status-update">Updates of company</label><br/><br/>\
                 <input id="<%= uid %>-job-posting" type="radio" name="<%= uid %>-event-type" value="job-posting"/> <label for="<%= uid %>-job-posting">Job offers (BETA)</label><br><br/>\
                 <input id="<%= uid %>-any" type="radio" name="<%= uid %>-event-type" checked checked value="any"/> <label for="<%= uid %>-any">Any</label>\
             </dd>\
             <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>cache_lifetime"><option value="5">Every 5 min</option> <option value="30" selected>Every 30 min</option> <option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>Posts to load during update</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>Moderation enabled</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
 </dl>\
 <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
     ',
soundcloudView: '\
         <div class="feed-view" data-feed-type="soundcloud" data-uid="<%= uid %>">\
             <h1>Content settings for SoundCloud feed</h1>\
             <dl class="section-settings">\
                 <dt class="">Username</dt>\
                 <dd class=""><input type="text" name="<%= uid %>-username" placeholder="What content to stream"/>\
                     <p class="desc">Enter SoundCloud username, eg. <strong>username</strong> in soundcloud.com/<strong>username</strong>/sets/playlist.</p>\
                 </dd>\
                 <dt class="">Playlist</dt>\
                 <dd class=""><input type="text" name="<%= uid %>-content" placeholder="What content to stream"/>\
                     <p class="desc">Enter playlist ID, eg. <strong>playlist</strong> in soundcloud.com/username/sets/<strong>playlist</strong>.</p>\
                 </dd>\
                 <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="5">Every 5 min</option> <option value="30" selected>Every 30 min</option> <option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>Posts to load during update</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>Moderation enabled</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
 </dl>\
 <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
     ',
filterView:     '\
         <div class="feed-view" data-filter-uid="<%= uid %>">\
     <h1>Moderation settings</h1>\
     <dl class="section-settings">\
 <dt class="">Content to exclude</dt>\
 <dd class=""><input type="text" name="<%= uid %>-filter-by-words" placeholder="What content to exclude"/>\
     <p class="desc">\
     1. To exclude posts by word in text enter any word<br>\
     2. To exclude by URL enter any substring with hash like this <strong>#badpost</strong> or <strong>#1234512345</strong><br>\
         3. To exclude by nickname enter word like this <strong>@nickname</strong><br>\
             4. You can enter multiple exclusion rules separated by comma without spaces eg <strong>anyword,@nickname,#URLpart</strong>\
             </p>\
         </dd>\
     </dl>\
     </div>'
}

ff_templates.stream = ff_templates.view;