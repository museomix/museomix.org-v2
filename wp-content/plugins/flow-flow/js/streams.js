var ff_templates = {
    streamRow:      '<td class="controls"><div class="loader-wrapper"><div class="throbber-loader"></div></div><i class="flaticon-tool_edit"></i> <i class="flaticon-tool_clone"></i> <i class="flaticon-tool_delete"></i></td><td><span class="cache-status-<%= status %>"></span></td><td class="td-name"><%= name %></td> <td class="td-type"><%= layout %></span></td> <td class="td-feed"><%= feeds %></td><td><span class="shortcode">[ff id="<%= id %>"]</span></td>',
    streamRowEmpty: '<tr class="empty-row"><td class="empty-cell" colspan="6">Please create at least one stream</td></tr>',
    listRowEmpty: '<tr><td  class="empty-cell" colspan="4">Add at least one feed</td></tr>',

    view:           '<input type="hidden" name="stream-<%= id %>-id" class="stream-id-value" value="<%= id %>"/>\
                <div class="section clearfix" id="stream-name-<%= id %>">\
                    <h1 class="float-left"><%= header %><span class="admin-button grey-button button-go-back">Go back to list</span></h1>\
                    <p class="float-left input-not-obvious"><input type="text" name="stream-<%= id %>-name" placeholder="Type name and hit Enter..."/>\
                    <ul class="view-tabs float-left"><li class="tab-cursor"></li><li data-tab="source">source</li><li data-tab="general">general</li><%= TVtab %><li data-tab="grid">layout</li><li data-tab="stylings">styling</li><li data-tab="css">css</li><li data-tab="shortcode">shortcode</li></ul>\
                </div>\
                <div class="section" id="stream-feeds-<%= id %>" data-tab="source">\
                    <input type="hidden" name="stream-<%= id %>-feeds"/>\
                    <h1 class="desc-following">Connected feeds</h1>\
                    <p class="desc">Here you can connect feeds created on <a class="ff-pseudo-link" href="#sources-tab">Feeds tab</a>. To detach feed click feed label.</p>\
        <div class="stream-feeds">\
            <div class="stream-feeds__block"><span class="stream-feeds__add">+ Connect new feed to stream</span></div>\
            <div class="stream-feeds__select"><select></select><span class="stream-feeds__btn stream-feeds__ok"><i class="flaticon-plus"></i></span><span class="stream-feeds__btn stream-feeds__close"><i class="flaticon-cross"></i></span></div>\
            <div class="stream-feeds__list"></div>\
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
                            <p class="desc">Total number of visible posts from all connected feeds. "Show more" button appears if there are more posts loaded and cached.</p>\
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
                            <p class="desc">If disabled, click on the post opens original post URL.</p></dt>\
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
                        <dt class="multiline">Show only media posts<p class="desc">Display posts with images/video only.</p></dt>\
                        <dd>\
                            <label for="stream-<%= id %>-show-only-media-posts"><input id="stream-<%= id %>-show-only-media-posts" class="switcher" type="checkbox" name="stream-<%= id %>-show-only-media-posts" value="yep"/><div><div></div></div></label>\
                        </dd>\
                        <dt class="multiline">Titles link<p class="desc">Visit original post URL by clicking on post title, even if lightbox is enabled.</p></dt>\
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
                        <dt class="multiline">Max image resolution<p class="desc">Use only for streams with large-sized posts. Not recommended for default stream design.</p></dt>\
                        <dd>\
                        <label for="stream-<%= id %>-max-res"><input id="stream-<%= id %>-max-res" class="switcher" type="checkbox" name="stream-<%= id %>-max-res" value="nope"/><div><div></div></div></label>\
                        </dd>\
                    </dl>\
                    <span id="stream-settings-sbmt-<%= id %>" class="admin-button green-button submit-button">Save Changes</span>\
                </div>\
                                  <%= TV %>\
<div class="section grid-layout-chosen"  data-tab="grid" id="cont-settings-<%= id %>">\
<div class="design-step-1">\
    <h1 class="desc-following">Stream layout</h1>\
    <p class="desc">Each layout offers unique design and set of styling options.</p>\
    <div class="choose-wrapper">\
        <input name="stream-<%= id %>-layout" class="clearcache" id="stream-layout-masonry-<%= id %>" type="radio" value="masonry" checked/><label for="stream-layout-masonry-<%= id %>"><span class="choose-button"><i class="sprite-masonry"></i>Masonry</span><br><span class="desc">Pinterest-like layout with flexible post height (depending on post content).</span></label>\
        <input name="stream-<%= id %>-layout" class="clearcache" id="stream-layout-grid-<%= id %>" type="radio" value="grid" /><label for="stream-layout-grid-<%= id %>"><span class="choose-button"><i class="sprite-grid"></i>Grid</span><br><span class="desc">Classic grid with posts of the same height. Recommended for posts of similar format.</span></label>\
        <input name="stream-<%= id %>-layout" class="clearcache" id="stream-layout-justified-<%= id %>" type="radio" value="justified"/><label for="stream-layout-justified-<%= id %>"><span class="choose-button"><i class="sprite-justified"></i>Justified</span><br><span class="desc">One-height posts with fluid adjustable width. Only for image posts. Content always overlays.</span></label>\
        </div>\
    </div>\
    <dl class="section-settings settings-masonry">\
<dt class="multiline">Gallery mode\
        <p class="desc">Affects media posts only. Enable if post content overlays post image on mouseover / on touch.</p>\
    </dt>\
    <dd>\
        <label for="stream-<%= id %>-m-overlay"><input id="stream-<%= id %>-m-overlay" class="switcher" type="checkbox" name="stream-<%= id %>-m-overlay" value="yep"/><div><div></div></div></label>\
    </dd>\
    <dt class="multiline">Responsive settings\
        <p class="desc">Set number of columns and gaps between stream posts for various screen sizes.</p>\
    </dt>\
    <dd class="device-list">\
        <div><i class="flaticon-desktop"></i> <input name="stream-<%= id %>-m-c-desktop" id="stream-<%= id %>-m-c-desktop" type="range" min="1" max="12" step="1" value="5" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-m-s-desktop" name="stream-<%= id %>-m-s-desktop" value="0" class="extra-small"> px gaps</div>\
        <div><i class="flaticon-laptop"></i> <input name="stream-<%= id %>-m-c-laptop" id="stream-<%= id %>-m-c-laptop" type="range" min="1" max="12" step="1" value="4" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-m-s-laptop" name="stream-<%= id %>-m-s-laptop" value="0" class="extra-small"> px gaps</div>\
        <div><i class="flaticon-tablet rotated"></i> <input name="stream-<%= id %>-m-c-tablet-l" id="stream-<%= id %>-m-c-tablet-l" type="range" min="1" max="12" step="1" value="3" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-m-s-tablet-l" name="stream-<%= id %>-m-s-tablet-l" value="0" class="extra-small"> px gaps</div>\
        <div><i class="flaticon-tablet"></i> <input name="stream-<%= id %>-m-c-tablet-p" id="stream-<%= id %>-m-c-tablet-p" type="range" min="1" max="12" step="1" value="2" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-m-s-tablet-p" name="stream-<%= id %>-m-s-tablet-p" value="0" class="extra-small"> px gaps</div>\
        <div><i class="flaticon-phone2 rotated"></i> <input name="stream-<%= id %>-m-c-smart-l" id="stream-<%= id %>-m-c-smart-l" type="range" min="1" max="12" step="1" value="2" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-m-s-smart-l" name="stream-<%= id %>-m-s-smart-l" value="0" class="extra-small"> px gaps</div>\
        <div><i class="flaticon-phone2"></i> <input name="stream-<%= id %>-m-c-smart-p" id="stream-<%= id %>-m-c-smart-p" type="range" min="1" max="12" step="1" value="1" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-m-s-smart-p" name="stream-<%= id %>-m-s-smart-p" value="0" class="extra-small"> px gaps</div>\
    </dd>\
    </dl>\
    <dl class="section-settings settings-grid">\
    <dt class="multiline">Gallery mode\
        <p class="desc">Affects media posts only. Enable if post content overlays post image on mouseover / on touch.</p>\
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
        <p class="desc">Set number of columns and gaps between stream posts for various screen sizes.</p>\
    </dt>\
    <dd class="device-list">\
        <div><i class="flaticon-desktop"></i> <input name="stream-<%= id %>-c-desktop" id="stream-<%= id %>-c-desktop" type="range" min="1" max="12" step="1" value="5" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-s-desktop" name="stream-<%= id %>-s-desktop" value="0" class="extra-small"> px gaps</div>\
        <div><i class="flaticon-laptop"></i> <input name="stream-<%= id %>-c-laptop" id="stream-<%= id %>-c-laptop" type="range" min="1" max="12" step="1" value="4" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-s-laptop" name="stream-<%= id %>-s-laptop" value="0" class="extra-small"> px gaps</div>\
        <div><i class="flaticon-tablet rotated"></i> <input name="stream-<%= id %>-c-tablet-l" id="stream-<%= id %>-c-tablet-l" type="range" min="1" max="12" step="1" value="3" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-s-tablet-l" name="stream-<%= id %>-s-tablet-l" value="0" class="extra-small"> px gaps</div>\
        <div><i class="flaticon-tablet"></i> <input name="stream-<%= id %>-c-tablet-p" id="stream-<%= id %>-c-tablet-p" type="range" min="1" max="12" step="1" value="2" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-s-tablet-p" name="stream-<%= id %>-s-tablet-p" value="0" class="extra-small"> px gaps</div>\
        <div><i class="flaticon-phone2 rotated"></i> <input name="stream-<%= id %>-c-smart-l" id="stream-<%= id %>-c-smart-l" type="range" min="1" max="12" step="1" value="2" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-s-smart-l" name="stream-<%= id %>-s-smart-l" value="0" class="extra-small"> px gaps</div>\
        <div><i class="flaticon-phone2"></i> <input name="stream-<%= id %>-c-smart-p" id="stream-<%= id %>-c-smart-p" type="range" min="1" max="12" step="1" value="1" data-rangeslider> <span class="range-value"></span> with <input type="text" id="stream-<%= id %>-s-smart-p" name="stream-<%= id %>-s-smart-p" value="0" class="extra-small"> px gaps</div>\
    </dd>\
    </dl>\
<dl class="section-settings settings-justified">\
    <dt class="multiline">Responsive settings\
        <p class="desc">Set number of columns and gaps between stream posts for various screen sizes.</p>\
    </dt>\
    <dd class="device-list">\
        <div><i class="flaticon-desktop"></i> Preferred row height is <input type="text" id="stream-<%= id %>-j-h-desktop" name="stream-<%= id %>-j-h-desktop" value="260" class="short"> px with <input type="text" id="stream-<%= id %>-j-s-desktop" name="stream-<%= id %>-j-s-desktop" value="0" class="extra-small"> px gaps</div>\
        <div><i class="flaticon-laptop"></i> Preferred row height is <input type="text" id="stream-<%= id %>-j-h-laptop" name="stream-<%= id %>-j-h-laptop" value="240" class="short"> px with <input type="text" id="stream-<%= id %>-j-s-laptop" name="stream-<%= id %>-j-s-laptop" value="0" class="extra-small"> px gaps</div>\
        <div><i class="flaticon-tablet rotated"></i> Preferred row height is <input type="text" id="stream-<%= id %>-j-h-tablet-l" name="stream-<%= id %>-j-h-tablet-l" value="220" class="short"> px with <input type="text" id="stream-<%= id %>-j-s-tablet-l" name="stream-<%= id %>-j-s-tablet-l" value="0" class="extra-small"> px gaps</div>\
        <div><i class="flaticon-tablet"></i> Preferred row height is <input type="text" id="stream-<%= id %>-j-h-tablet-p" name="stream-<%= id %>-j-h-tablet-p" value="200" class="short"> px with <input type="text" id="stream-<%= id %>-j-s-tablet-p" name="stream-<%= id %>-j-s-tablet-p" value="0" class="extra-small"> px gaps</div>\
        <div><i class="flaticon-phone2 rotated"></i> Preferred row height is <input type="text" id="stream-<%= id %>-j-h-smart-l" name="stream-<%= id %>-j-h-smart-l" value="180" class="short"> px with <input type="text" id="stream-<%= id %>-j-s-smart-l" name="stream-<%= id %>-j-s-smart-l" value="0" class="extra-small"> px gaps</div>\
        <div><i class="flaticon-phone2"></i> Preferred row height is <input type="text" id="stream-<%= id %>-j-h-smart-p" name="stream-<%= id %>-j-h-smart-p" value="160" class="short"> px with <input type="text" id="stream-<%= id %>-j-s-smart-p" name="stream-<%= id %>-j-s-smart-p" value="0" class="extra-small"> px gaps</div>\
    </dd>\
    </dl>\
<div class="button-wrapper"><span id="stream-layout-sbmt-<%= id %>" class="admin-button green-button submit-button" style="margin-bottom:35px">Save Changes</span></div>\
<h1>Layout Design Settings</h1>\
<dl class="section-settings section-compact">\
    <dt class="multiline">Stream heading\
        <p class="desc">Leave empty to not show.</p></dt>\
    <dd>\
        <input id="stream-<%= id %>-heading" type="text" name="stream-<%= id %>-heading" placeholder="Enter heading"/>\
    </dd>\
    <dt class="multiline">Heading color\
        <p class="desc"></p>\
    </dt>\
    <dd>\
        <input id="heading-color-<%= id %>" data-color-format="rgba" name="stream-<%= id %>-headingcolor" type="text" value="rgb(59, 61, 64)" tabindex="-1">\
        </dd>\
        <dt>Stream subheading</dt>\
        <dd>\
            <input id="stream-<%= id %>-subheading" type="text" name="stream-<%= id %>-subheading" placeholder="Enter subheading"/>\
        </dd>\
        <dt class="multiline">Subheading color\
            <p class="desc"></p>\
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
                <p class="desc"></p>\
            </dt>\
            <dd>\
                <input data-prop="backgroundColor" id="bg-color-<%= id %>" data-color-format="rgba" name="stream-<%= id %>-bgcolor" type="text" value="rgb(240, 240, 240)" tabindex="-1">\
                </dd>\
                <dt class="multiline">SORTING AND SEARCH BAR\
                <p class="desc"></p>\
                </dt>\
                <dd>\
                    <label for="stream-<%= id %>-filter"><input id="stream-<%= id %>-filter" class="switcher" type="checkbox" name="stream-<%= id %>-filter" checked value="yep"/><div><div></div></div></label>\
                </dd>\
                <dt>Filters and controls color\
                </dt>\
                <dd>\
                    <input id="filter-color-<%= id %>" data-color-format="rgba" name="stream-<%= id %>-filtercolor" type="text" value="rgb(205, 205, 205)" tabindex="-1">\
                    </dd>\
                    <dt class="multiline">Slider on mobiles <p class="desc">Stream will turn into a slider with 3 items per slide on mobile devices.</p></dt>\
                    <dd>\
                        <label for="stream-<%= id %>-mobileslider"><input id="stream-<%= id %>-mobileslider" class="switcher" type="checkbox" name="stream-<%= id %>-mobileslider" value="yep"/><div><div></div></div></label>\
                    </dd>\
                    <dt class="multiline">Animate stream items <p class="desc">Posts are revealed with animation effect if they appear in viewport. Otherwise all posts are visible immediately.</p></dt>\
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
    <dt><span class="valign">AVATAR STYLE</span></dt>\
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
    <dt class="multiline">\
        SHAPE STYLE\
        <p class="desc">Common setting for avatar shape and card corners.</p>\
    </dt>\
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
    <dt><span class="valign">Card background color</span></dt>\
    <dd>\
        <input data-prop="backgroundColor" id="card-color-<%= id %>" data-color-format="rgba" name="stream-<%= id %>-cardcolor" type="text" value="rgb(255,255,255)" tabindex="-1">\
        </dd>\
        <dt class="multiline">ACCENT COLOR\
            <p class="desc">Applies to post heading, name and social buttons hover effect.</p>\
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
                    <dt class="multiline">SECONDARY COLOR\
                        <p class="desc">Applies to timestamp and social counters.</p></dt>\
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
                                <dt><span class="valign">COUNTER ICONS STYLE</span></dt>\
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
    <a href="http://go.social-streams.com/help">Help Center</a><br>\
    <a href="https://social-streams.com/">Social Stream Apps</a><br>\
    <a href="http://go.social-streams.com/twitter">Twitter</a><br>\
    <a href="http://go.social-streams.com/facebook">Facebook</a>\
    </div>\
    </div>\
    </div>\
    </div>',
    twitterView:    '\
<div class="feed-view" data-feed-type="twitter" data-uid="<%= uid %>">\
<h1>Twitter feed settings</h1>\
<dl class="section-settings">\
<dt>FEED TYPE </dt>\
<dd>\
<input id="<%= uid %>-home-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="home_timeline" checked/>\
<label for="<%= uid %>-home-timeline-type">Home timeline</label><br><br>\
<input id="<%= uid %>-user-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="user_timeline" />\
<label for="<%= uid %>-user-timeline-type">User feed</label><br><br>\
<input id="<%= uid %>-search-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="search"/>\
<label for="<%= uid %>-search-timeline-type">Tweets by search</label><br><br>\
<input id="<%= uid %>-list-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="list_timeline"/>\
<label for="<%= uid %>-list-timeline-type">User list</label><br><br>\
<input id="<%= uid %>-fav-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="favorites"/>\
<label for="<%= uid %>-fav-timeline-type">User\'s likes</label><br><br>\
</dd>\
<dt>\
Content to show\
<div class="desc hint-block">\
    <span class="hint-link">\
        <img src="<%= plugin_url %>/assets/info_icon.svg">\
    </span>\
    <div class="hint hint-pro">\
        <h1>Content to show</h1>\
        <ul>\
            <li><b>Home timeline</b> — enter your own username.</li>\
            <li><b>User feed</b> — enter username of any public Twitter account.</li>\
            <li><b>Tweets by search</b> — enter any word or #hashtag. <a href="https://developer.twitter.com/en/docs/api-reference-index" target="_blank">Advanced search terms</a>.</li>\
            <li><b>User list</b> — enter username here and List name in corresponding field below.</li>\
            <li><b>User’s likes</b> —  enter username.</li>\
        </ul>\
    </div>\
</div>\
</dt>\
<dd><input type="text" name="<%= uid %>-content"/></dd>\
<dt>\
    List name\
    <p class="desc">Required if you choose list feed.</p>\
</dt>\
<dd><input type="text" name="<%= uid %>-list-name" placeholder=""/></dd>\
<dt class="">\
    Tweets language\
    <p class="desc">As detected by Twitter. Only for search feeds.</p>\
</dt>\
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
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>\
Posts to load during update\
<p class="desc">The first load is always 50. <a href="http://docs.social-streams.com/article/137-managing-feed-updates" target="_blank">Learn more</a>.</p>\
</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>\
    MODERATE THIS FEED\
    <p class="desc"><a href="http://docs.social-streams.com/article/70-manual-premoderation" target="_blank">Learn more</a></p>\
</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
</dl>\
<input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
',
    facebookView:   '\
<div class="feed-view"  data-feed-type="facebook" data-uid="<%= uid %>">\
<h1>Facebook feed settings</h1>\
<dl class="section-settings">\
<dt>FEED TYPE </dt>\
<dd>\
<input id="<%= uid %>-page-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="page_timeline" checked />\
<label for="<%= uid %>-page-timeline-type">Page</label>\
<input class="hide" id="<%= uid %>-group-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="group" />\
<label class="hide" for="<%= uid %>-group-timeline-type">Group</label><br><br>\
<input id="<%= uid %>-album-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="album" />\
<label for="<%= uid %>-album-timeline-type">Album</label>\
</dd>\
<dt>\
Content to show\
<div class="desc hint-block">\
    <span class="hint-link">\
        <img src="<%= plugin_url %>/assets/info_icon.svg">\
    </span>\
    <div class="hint hint-pro">\
        <h1>Content to show</h1>\
        <ul>\
            <li><b>Page</b> — enter nickname of any public page or Page ID.</li>\
            <li class="hide"><b>Group</b> — enter Group ID.</li>\
            <li><b>Album</b> — enter Album ID. <a href="http://docs.social-streams.com/article/50-find-facebook-album-id" target="_blank">What is it?</a> </li>\
        </ul><br>\
        Use <a href="http://lookup-id.com" target="_blank">Find my Facebook ID</a> tool to find your Page ID or Group ID.\
    </div>\
</div>\
</dt>\
<dd><input type="text" name="<%= uid %>-content"/></dd>\
<dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>\
Posts to load during update\
<p class="desc">The first load is always 50. <a href="http://docs.social-streams.com/article/137-managing-feed-updates" target="_blank">Learn more</a>.</p>\
</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>\
    MODERATE THIS FEED\
    <p class="desc"><a href="http://docs.social-streams.com/article/70-manual-premoderation" target="_blank">Learn more</a></p>\
</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
</dl>\
<input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
                    </div>\
',
    vimeoView:      '\
<div class="feed-view"  data-feed-type="vimeo" data-uid="<%= uid %>">\
<h1>Vimeo feed settings</h1>\
<dl class="section-settings">\
<dt>Feed type </dt>\
<dd>\
<input id="<%= uid %>-type-videos" type="radio" name="<%= uid %>-timeline-type" value="videos" checked/>\
<label for="<%= uid %>-type-videos">User videos</label><br><br>\
<input id="<%= uid %>-type-likes" type="radio" name="<%= uid %>-timeline-type" value="likes" />\
<label for="<%= uid %>-type-likes">Liked videos</label><br><br>\
<input id="<%= uid %>-type-channel" type="radio" name="<%= uid %>-timeline-type" value="channel" />\
<label for="<%= uid %>-type-channel">Channel</label><br><br>\
<input id="<%= uid %>-type-group" type="radio" name="<%= uid %>-timeline-type" value="group" />\
<label for="<%= uid %>-type-group">Group</label><br><br>\
<input id="<%= uid %>-type-album" type="radio" name="<%= uid %>-timeline-type" value="album" />\
<label for="<%= uid %>-type-album">Album</label>\
</dd>\
<dt>\
    Content to show\
    <div class="desc hint-block">\
        <span class="hint-link">\
            <img src="<%= plugin_url %>/assets/info_icon.svg">\
        </span>\
        <div class="hint hint-pro">\
            <h1>Content to show</h1>\
            <ul>\
                <li><b>User videos</b> — enter nickname of Vimeo user.</li>\
                <li><b>Liked videos</b> — enter nickname of Vimeo user.</li>\
                <li><b>Channel</b> — enter nickname of Vimeo channel.</li>\
                <li><b>Group</b> — enter nickname of Vimeo group.</li>\
                <li><b>Album</b> — enter nickname of Vimeo album.</li>\
            </ul>\
        </div>\
    </div>\
</dt>\
<dd><input type="text" name="<%= uid %>-content"/></dd>\
<dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>MODERATE THIS FEED</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
</dl>\
<input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
',
    googleView:     '\
<div class="feed-view" data-feed-type="google" data-uid="<%= uid %>">\
  <h1>Google+ feed settings</h1>\
  <dl class="section-settings">\
      <dt>\
        Content to show\
        <div class="desc hint-block">\
            <span class="hint-link">\
                <img src="<%= plugin_url %>/assets/info_icon.svg">\
            </span>\
            <div class="hint hint-pro">\
                <h1>Content to show</h1>\
                Google username starting with plus or numeric ID of your page.\
            </div>\
        </div>\
      </dt>\
      <dd><input type="text" name="<%= uid %>-content" placeholder="+UserName"/></dd>\
      <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>\
Posts to load during update\
<p class="desc">The first load is always 50. <a href="http://docs.social-streams.com/article/137-managing-feed-updates" target="_blank">Learn more</a>.</p>\
</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>\
    MODERATE THIS FEED\
    <p class="desc"><a href="http://docs.social-streams.com/article/70-manual-premoderation" target="_blank">Learn more</a></p>\
</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
  </dl>\
  <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
',
    rssView:        '\
  <div class="feed-view"  data-feed-type="rss" data-uid="<%= uid %>">\
      <h1>RSS feed settings</h1>\
      <dl class="section-settings">\
          <dt class="">RSS CHANNEL URL</dt>\
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
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>\
    MODERATE THIS FEED\
    <p class="desc"><a href="http://docs.social-streams.com/article/70-manual-premoderation" target="_blank">Learn more</a></p>\
</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
      </dl>\
      <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
  </div>\
',
    pinterestView:  '\
  <div class="feed-view" data-feed-type="pinterest" data-uid="<%= uid %>">\
      <h1>Pinterest feed settings</h1>\
      <dl class="section-settings">\
          <dt class="">\
            Content to show\
            <div class="desc hint-block">\
                <span class="hint-link">\
                    <img src="<%= plugin_url %>/assets/info_icon.svg">\
                </span>\
                <div class="hint hint-pro">\
                    <h1>Content to show</h1>\
                    <ul>\
                        <li><b>User feed</b> — enter username e.g. <i>elainen</i>.</li>\
                        <li><b>User board</b> — enter user board slug e.g. <i>elainen/cute-animals</i>.</li>\
                    </ul>\
                </div>\
            </div>\
          </dt>\
          <dd class=""><input type="text" name="<%= uid %>-content"/></dd>\
              <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>\
    MODERATE THIS FEED\
    <p class="desc"><a href="http://docs.social-streams.com/article/70-manual-premoderation" target="_blank">Learn more</a></p>\
</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
      </dl>\
      <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
  </div>\
                      ',
    instagramView:  '\
  <div class="feed-view" data-feed-type="instagram" data-uid="<%= uid %>">\
      <h1>Instagram feed settings</h1>\
      <dl class="section-settings">\
          <dt>FEED TYPE</dt>\
          <dd>\
          <input id="<%= uid %>-user-timeline-type" checked type="radio" name="<%= uid %>-timeline-type" value="user_timeline"/>\
            <label for="<%= uid %>-user-timeline-type">User feed</label>\
          <input class="hide" id="<%= uid %>-likes-type"  type="radio" name="<%= uid %>-timeline-type" value="likes"/>\
            <label class="hide" for="<%= uid %>-likes-type">User\'s likes</label><br><br>\
          <input id="<%= uid %>-search-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="tag"/>\
            <label for="<%= uid %>-search-timeline-type">Hashtag</label><br><br>\
          <input id="<%= uid %>-location-type" type="radio" name="<%= uid %>-timeline-type" value="location"/>\
            <label for="<%= uid %>-location-type">Location</label><br><br>\
          </dd>\
          <dt>\
            Content to show\
            <div class="desc hint-block">\
                <span class="hint-link">\
                    <img src="<%= plugin_url %>/assets/info_icon.svg">\
                </span>\
                <div class="hint hint-pro">\
                    <h1>Content to show</h1>\
                    <ul>\
                        <li><b>User feed</b> — enter nickname of any public Instagram account.</li>\
                        <li class="hide"><b>User\'s likes</b> — enter nickname of your own account.</li>\
                        <li><b>Hashtag</b> — enter a hashtag.</li>\
                        <li><b>Location</b> — enter <a href="http://docs.social-streams.com/article/118-find-instagram-location-id" target="_blank">Location ID</a>.</li>\
                    </ul>\
                </div>\
            </div>\
          </dt>\
          <dd>\
              <input type="text" name="<%= uid %>-content"/>\
                      </dd>\
          <dt class="hide">API METHODS\
          <div class="desc hint-block">\
                <span class="hint-link">\
                    <img src="<%= plugin_url %>/assets/info_icon.svg">\
                </span>\
                <div class="hint hint-pro">\
                    <h1>Which API to choose</h1>\
                    <ul>\
                        <li><b>Official</b> — best way to stream posts of account for which you obtained token.</li>\
                        <li><b>JSON</b> — unofficial method to stream posts of other accounts but only latest 13 posts and no video posts available for this method.</li>\
                        <li><b>Graph</b> — this unofficial API is subject of fluent changes and requires updates to work but allows pagination and video posts.</li>\
                    </ul>\
                </div>\
            </div>\
          </dt>\
          <dd class="hide" style="margin-top: 8px">\
          <input id="<%= uid %>-api-official" checked type="radio" name="<%= uid %>-api-type" value="official"/>\
            <label for="<%= uid %>-api-official">Official API</label><br><br>\
          <input id="<%= uid %>-api-json" type="radio" name="<%= uid %>-api-type" value="json"/>\
            <label for="<%= uid %>-api-json">JSON API</label><br><br>\
          <input id="<%= uid %>-api-graph" type="radio" name="<%= uid %>-api-type" value="graph"/>\
            <label for="<%= uid %>-api-graph">Graph API (experimental)</label><br><br>\
          </dd>\
                      <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt class="hide" >\
    Posts to load during update\
    <p class="desc">The first load is always 50. <a href="http://docs.social-streams.com/article/137-managing-feed-updates" target="_blank">Learn more</a>.</p>\
</dt>\
<dd class="hide" >\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>\
    MODERATE THIS FEED\
    <p class="desc"><a href="http://docs.social-streams.com/article/70-manual-premoderation" target="_blank">Learn more</a></p>\
</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
  </dl>\
  <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
                                                      ',
    wordpressView:  '\
<div class="feed-view" data-feed-type="wordpress" data-uid="<%= uid %>">\
  <h1>WordPress feed settings</h1>\
  <dl class="section-settings">\
      <dt>Show latest</dt>\
      <dd>\
          <input id="<%= uid %>-wordpress-posts" type="radio" name="<%= uid %>-wordpress-type" checked value="posts"/> <label for="<%= uid %>-wordpress-posts">Posts</label>\
          <input id="<%= uid %>-wordpress-comments" type="radio" name="<%= uid %>-wordpress-type" value="comments"/> <label for="<%= uid %>-wordpress-comments">Comments</label>\
      </dd>\
      <dt>\
        Category\
        <p class="desc">Works for posts only. Enter one or multiple categories separated by commas, or leave empty to show all posts.</p>\
      </dt>\
      <dd>\
          <input type="text" name="<%= uid %>-category-name" placeholder="Category name"/>\
          </dd>\
          <dt>\
            SPECIFIC POST COMMENTS\
            <p class="desc">Enter post ID to show comments from a single post.</p>\
          </dt>\
          <dd>\
              <input type="text" name="<%= uid %>-post-id" placeholder="Post ID"/>\
              </dd>\
              <dt>\
                Custom post slug\
                <p class="desc">Display only custom posts of specific type.</p>\
              </dt>\
              <dd>\
                  <input type="text" name="<%= uid %>-slug" placeholder="Custom post slug"/>\
              </dd>\
              <dt>\
                Shortcodes in post\
                <p class="desc">! We don’t guarantee compatibility with any shortcodes if you choose expanding option.</p>\
              </dt>\
              <dd>\
              <input id = "<%= uid %>-strip" type = "radio" name = "<%= uid %>-shortcodes" checked value = "strip" /> <label for="<%= uid %>-strip">Remove shortcodes</label>\
              <input id="<%= uid %>-expand" type="radio" name="<%= uid %>-shortcodes" value="expand"/> <label for="<%= uid %>-expand">Expand shortcodes</label>\
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
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>\
Posts to load during update\
<p class="desc">The first load is always 50. <a href="http://docs.social-streams.com/article/137-managing-feed-updates" target="_blank">Learn more</a>.</p>\
</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>\
    MODERATE THIS FEED\
    <p class="desc"><a href="http://docs.social-streams.com/article/70-manual-premoderation" target="_blank">Learn more</a></p>\
</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
</dl>\
<input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
              ',
    youtubeView:    '\
  <div class="feed-view" data-feed-type="youtube" data-uid="<%= uid %>">\
      <h1>YouTube feed settings</h1>\
      <dl class="section-settings">\
          <dt>FEED TYPE</dt>\
          <dd>\
              <input id="<%= uid %>-user-timeline-type"  type="radio" name="<%= uid %>-timeline-type" value="user_timeline" checked/>\
              <label for="<%= uid %>-user-timeline-type">User feed</label><br><br>\
              <input id="<%= uid %>-channel-type"  type="radio" name="<%= uid %>-timeline-type" value="channel"/>\
              <label for="<%= uid %>-channel-type">Channel</label><br><br>\
                  <input id="<%= uid %>-pl-type"  type="radio" name="<%= uid %>-timeline-type" value="playlist"/>\
                  <label for="<%= uid %>-pl-type">Playlist</label><br><br>\
                      <input id="<%= uid %>-search-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="search"/>\
                      <label for="<%= uid %>-search-timeline-type">Search</label>\
                  </dt>\
                      <dt class="">\
                        Content to show\
                        <div class="desc hint-block">\
                            <span class="hint-link">\
                                <img src="<%= plugin_url %>/assets/info_icon.svg">\
                            </span>\
                            <div class="hint hint-pro">\
                                <h1>Content to show</h1>\
                                <ul>\
                                    <li><b>User feed</b> — enter YouTube username with public access.</li>\
                                    <li><b>Channel</b> — enter channel ID. <a href="https://support.google.com/youtube/answer/3250431?hl=en" target="_blank">What is it?</a></li>\
                                    <li><b>Playlist</b> — enter playlist ID. <a href="http://docs.social-streams.com/article/139-find-youtube-playlist-id" target="_blank">What is it?</a></li>\
                                    <li><b>Search</b> — enter any search query.</li>\
                                </ul>\
                            </div>\
                        </div>\
                      </dt>\
                      <dd class=""><input type="text" name="<%= uid %>-content"/></dd>\
                      <dt>Playlist reverse order</dt>\
      <dd>\
          <label for="<%= uid %>-playlist-order">\
              <input id="<%= uid %>-playlist-order" class="switcher" type="checkbox" name="<%= uid %>-playlist-order" value="yep"/> <div><div></div></div>\
          </label>\
      </dd>\
      <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>\
Posts to load during update\
<p class="desc">The first load is always 50. <a href="http://docs.social-streams.com/article/137-managing-feed-updates" target="_blank">Learn more</a>.</p>\
</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>\
    MODERATE THIS FEED\
    <p class="desc"><a href="http://docs.social-streams.com/article/70-manual-premoderation" target="_blank">Learn more</a></p>\
</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
</dl>\
<input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
      ',
    vineView:       '\
<div class="feed-view" data-feed-type="vine" data-uid="<%= uid %>">\
<h1>Vine feed settings</h1>\
<dl class="section-settings">\
 <dt>FEED TYPE</dt>\
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
             <input type="text" name="<%= uid %>-content"/>\
             <p class="desc">\
             1. For user timeline enter Vine account username or ID <a target="_blank" href="http://docs.social-streams.com/article/52-find-vine-id">See instructions</a><br>\
             2. For liked timeline enter Vine account username or ID <a target="_blank" href="http://docs.social-streams.com/article/52-find-vine-id">See instructions</a><br>\
                 3. To stream posts by hashtag enter one word without #\
                 </p>\
             </dd>\
             <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>\
Posts to load during update\
<p class="desc">The first load is always 50. <a href="http://docs.social-streams.com/article/137-managing-feed-updates" target="_blank">Learn more</a>.</p>\
</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>\
    MODERATE THIS FEED\
    <p class="desc"><a href="http://docs.social-streams.com/article/70-manual-premoderation" target="_blank">Learn more</a></p>\
</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
 </dl>\
 <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
     ',
    dribbbleView:   '\
     <div class="feed-view" data-feed-type="dribbble" data-uid="<%= uid %>">\
         <h1>Dribbble feed settings</h1>\
     <dl class="section-settings">\
         <dt>FEED TYPE</dt>\
         <dd>\
             <input id="<%= uid %>-user-timeline-type"  type="radio" name="<%= uid %>-timeline-type" value="user_timeline" checked/>\
             <label for="<%= uid %>-user-timeline-type">User feed</label><br><br>\
             <input id="<%= uid %>-popular-timeline-type" type="radio" name="<%= uid %>-timeline-type" value="liked"/>\
             <label for="<%= uid %>-popular-timeline-type">Liked shots</label><br><br>\
             </dt>\
                 <dt class="">\
                    Content to show\
                    <p class="desc">Enter Dribbble username.</p>\
                 </dt>\
                 <dd class=""><input type="text" name="<%= uid %>-content"/></dd>\
                 <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>\
Posts to load during update\
<p class="desc">The first load is always 50. <a href="http://docs.social-streams.com/article/137-managing-feed-updates" target="_blank">Learn more</a>.</p>\
</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>\
    MODERATE THIS FEED\
    <p class="desc"><a href="http://docs.social-streams.com/article/70-manual-premoderation" target="_blank">Learn more</a></p>\
</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
 </dl>\
 <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
                                                                                                                 ',
    foursquareView: '\
     <div class="feed-view" data-feed-type="foursquare" data-uid="<%= uid %>">\
         <h1>Foursquare feed settings</h1>\
         <dl class="section-settings">\
             <dt class="">\
                LOCATION ID\
                <p class="desc"><a href="http://docs.social-streams.com/article/116-find-foursquare-location-id" target="_blank">What is it?</a></p>\
             </dt>\
             <dd class=""><input type="text" name="<%= uid %>-content"/></dd>\
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
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>\
Posts to load during update\
<p class="desc">The first load is always 50. <a href="http://docs.social-streams.com/article/137-managing-feed-updates" target="_blank">Learn more</a>.</p>\
</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>\
    MODERATE THIS FEED\
    <p class="desc"><a href="http://docs.social-streams.com/article/70-manual-premoderation" target="_blank">Learn more</a></p>\
</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
 </dl>\
 <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
                                                                                                                 ',
    flickrView:     '\
     <div class="feed-view" data-feed-type="flickr" data-uid="<%= uid %>">\
         <h1>Flickr feed settings</h1>\
         <dl class="section-settings">\
             <dt>FEED TYPE</dt>\
             <dd>\
                 <input id="<%= uid %>-user_timeline" type="radio" checked name="<%= uid %>-timeline-type" value="user_timeline"/>\
                 <label for="<%= uid %>-user_timeline">User Photostream</label><br><br>\
                 <input id="<%= uid %>-tag" type="radio" name="<%= uid %>-timeline-type" value="tag"/>\
                 <label for="<%= uid %>-tag">Tag</label>\
             </dt>\
                 <dt class="">\
                    Content to show\
                    <div class="desc hint-block">\
                        <span class="hint-link">\
                            <img src="<%= plugin_url %>/assets/info_icon.svg">\
                        </span>\
                        <div class="hint hint-pro">\
                            <h1>Content to show</h1>\
                            <ul>\
                                <li><b>User Photostream</b> — enter Flickr username.</li>\
                                <li><b>Tag</b> — enter one or multiple words separated by commas.</li>\
                            </ul>\
                        </div>\
                    </div>\
                 </dt>\
                 <dd class=""><input type="text" name="<%= uid %>-content"/></dd>\
                 <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>\
    MODERATE THIS FEED\
    <p class="desc"><a href="http://docs.social-streams.com/article/70-manual-premoderation" target="_blank">Learn more</a></p>\
</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
     </dl>\
     <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
 </div>\
                                                                                                                             ',
tumblrView:     '\
     <div class="feed-view" data-feed-type="tumblr" data-uid="<%= uid %>">\
         <h1>Tumblr feed settings</h1>\
         <dl class="section-settings">\
             <dt class="">\
                Content to show\
                <div class="desc hint-block">\
                    <span class="hint-link">\
                        <img src="<%= plugin_url %>/assets/info_icon.svg">\
                    </span>\
                    <div class="hint hint-pro">\
                        <h1>Content to show</h1>\
                        Enter Tumblr username to show images from tlog.\
                    </div>\
                </div>\
             </dt>\
             <dd class=""><input type="text" name="<%= uid %>-content"/></dd>\
             <dt>Rich text</dt>\
             <dd>\
                 <label for="<%= uid %>-rich-text"><input id="<%= uid %>-rich-text" class="switcher" type="checkbox" name="<%= uid %>-rich-text" value="yep"/><div><div></div></div></label>\
             </dd>\
             <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>\
Posts to load during update\
<p class="desc">The first load is always 50. <a href="http://docs.social-streams.com/article/137-managing-feed-updates" target="_blank">Learn more</a>.</p>\
</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>\
    MODERATE THIS FEED\
    <p class="desc"><a href="http://docs.social-streams.com/article/70-manual-premoderation" target="_blank">Learn more</a></p>\
</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
 </dl>\
 <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
 ',
linkedinView:   '\
     <div class="feed-view" data-feed-type="linkedin" data-uid="<%= uid %>">\
         <h1>LinkedIn feed settings</h1>\
         <dl class="section-settings">\
             <dt class="">\
                COMPANY PAGE ID\
                <p class="desc"><a href="http://docs.social-streams.com/article/51-find-linkedin-id" target="_blank">What is it?</a></p>\
            </dt>\
             <dd class=""><input type="text" name="<%= uid %>-content"/></dd>\
             <dt>Event type</dt>\
             <dd>\
                 <input id="<%= uid %>-status-update" type="radio" name="<%= uid %>-event-type" value="status-update"/> <label for="<%= uid %>-status-update">Updates of company</label><br/><br/>\
                 <input id="<%= uid %>-job-posting" type="radio" name="<%= uid %>-event-type" value="job-posting"/> <label for="<%= uid %>-job-posting">Job offers (BETA)</label><br><br/>\
                 <input id="<%= uid %>-any" type="radio" name="<%= uid %>-event-type" checked checked value="any"/> <label for="<%= uid %>-any">Any</label>\
             </dd>\
             <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>cache_lifetime"><option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>\
Posts to load during update\
<p class="desc">The first load is always 50. <a href="http://docs.social-streams.com/article/137-managing-feed-updates" target="_blank">Learn more</a>.</p>\
</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>\
    MODERATE THIS FEED\
    <p class="desc"><a href="http://docs.social-streams.com/article/70-manual-premoderation" target="_blank">Learn more</a></p>\
</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
 </dl>\
 <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
     ',
soundcloudView: '\
         <div class="feed-view" data-feed-type="soundcloud" data-uid="<%= uid %>">\
             <h1>SoundCloud feed settings</h1>\
             <dl class="section-settings">\
                 <dt class="">\
                    Username\
                    <p class="desc">Find username in URL: soundcloud.com/<b>username</b>/sets/playlist.</p> \
                 </dt>\
                 <dd class=""><input type="text" name="<%= uid %>-username"/>\</dd>\
                 <dt class="">\
                    Playlist\
                    <p class="desc">Find playlist ID in URL: soundcloud.com/username/sets/<b>playlist</b>.</p>\
                 </dt>\
                 <dd class=""><input type="text" name="<%= uid %>-content"/></dd>\
                 <dt>Feed updates frequency</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-cache_lifetime" id="<%= uid %>-cache_lifetime"><option value="60">Every hour</option> <option value="360">Every 6 hours</option> <option value="1440">Once a day</option> <option value="10080">Once a week</option></select> </div></dd>\
<dt>\
Posts to load during update\
<p class="desc">The first load is always 50. <a href="http://docs.social-streams.com/article/137-managing-feed-updates" target="_blank">Learn more</a>.</p>\
</dt>\
<dd>\
<div class="select-wrapper"> <select name="<%= uid %>-posts" id="<%= uid %>-post"><option value="1">1 post</option><option value="5">5 posts</option><option selected value="10">10 posts</option><option value="20">20 posts</option></select></div>\
</dd>\
<dt>\
    MODERATE THIS FEED\
    <p class="desc"><a href="http://docs.social-streams.com/article/70-manual-premoderation" target="_blank">Learn more</a></p>\
</dt>\
<dd>\
<label for="<%= uid %>-mod"><input id="<%= uid %>-mod" class="switcher" type="checkbox" name="<%= uid %>-mod" value="yep"/> <div><div></div></div></label>\
</dd>\
 </dl>\
 <input type="hidden" id="<%= uid %>-enabled" value="yep" checked type="checkbox" name="<%= uid %>-enabled">\
</div>\
     ',
filterView:     '\
         <div class="feed-view filter-feed" data-filter-uid="<%= uid %>">\
             <h1>Filter Feed Content</h1>\
             <dl class="section-settings">\
                <dt class="">Exclude all</dt>\
                <dd class="">\
                    <input type="hidden" data-type="filter-exclude-holder" name="<%= uid %>-filter-by-words"/>\
                    <input type="text" data-action="add-filter" data-id="<%= uid %>" data-type="exclude" placeholder="Type and hit Enter"/>\
                    <ul class="filter-labels" data-type="exclude"></ul>\
                </dd>\
             </dl>\
             <dl class="section-settings">\
                <dt class="">Include all</dt>\
                <dd class="">\
                    <input type="hidden" data-type="filter-include-holder" name="<%= uid %>-include"/>\
                    <input type="text" data-action="add-filter" data-id="<%= uid %>" data-type="include" placeholder="Type and hit Enter"/>\
                    <ul class="filter-labels" data-type="include"></ul>\
                </dd>\
             </dl>\
             <div class="hint-block">\
                 <a class="hint-link" href="#" data-action="hint-toggle">How to Filter</a>\
                 <div class="hint">\
                    <h1>Hints on Filtering</h1>\
                    <div class="desc">\
                        <p>\
                        1. <strong>Filter by word</strong> — type any word<br>\
                        </p>\
                        <p>\
                        2. <strong>Filter by URL</strong> — enter any substring with hash like this #badpost or #1234512345<br>\
                        </p>\
                        <p>\
                        3. <strong>Filter by account</strong> — type word with @ symbol e.g. @apple<br>\
                        </p>\
                        <br>\
                        <p>\
                        <a target="_blank" title="Learn more" href="http://docs.social-streams.com/article/71-automatic-moderation-with-filters">Learn more</a>\
                        </p>\
                    </div>\
                </div>\
            </div>\
     </div>'
}

ff_templates.stream = ff_templates.view;