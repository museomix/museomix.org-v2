function ffpad(n) {
    return n < 10 ? '0' + n : n
}

var ff_newstemplate_success = '\
    <div class="news-container">\
        <div class="header">\
            <a href="https://social-streams.com/" target="_blank" title="Social Streams">\
                <img src="<%= assets_url %>/assets/social-streams-logo.svg" alt="">\
            </a>\
            <ul>\
                <% json.menu.forEach(function(item, i){ %>\
                <li>\
                    <a href="<%= item.link %>" target="_blank"><%= item.title %></a>\
                </li>\
                <% }) %>\
            </ul>\
        </div>\
        <div class="row">\
            <div class="block news">\
                <div class="news-wrapper">\
                    <div class="block-title">News</div>\
                    <div class="block-content">\
                        <ul>\
                            <% json.news.forEach(function(item, i) { %>\
                            <% var date = new Date(item.timestamp) %>\
                            <li>\
                                <div class="date"><%= ffpad(date.getDate()) %>.<%= ffpad(date.getMonth() + 1) %>.<%= date.getFullYear() %></div>\
                                <div class="title"><%= item.title %></div>\
                                <div class="content"><%= item.text %></div>\
                                <% if(item.link) { %>\
                                <div class="more-link">\
                                    <a href="<%= item.link %>" target="_blank" title="Learn more">Learn more</a>\
                                </div>\
                                <% } %>\
                            </li>\
                            <% }) %> \
                        </ul>\
                    </div>\
                </div>\
            </div>\
            <div class="block requirements">\
                <div class="news-wrapper">\
                    <div class="block-title">System Requirements</div>\
                    <div class="block-content">\
                        <ul>\
                            <li>\
                                <div class="title">WordPress</div>\
                                    <% if (requirements.wp_status) { %>\
                                    <div class="content success">✔︎\
                                    <% } else { %>\
                                    <div class="content fail">✘\
                                    <% } %>\
                                    <span>Version <%= requirements.wp %></span>\
                                </div>\
                            </li>\
                            <li>\
                                <div class="title">PHP</div>\
                                    <% if (requirements.php_status) { %>\
                                    <div class="content success">✔︎\
                                    <% } else { %>\
                                    <div class="content fail">✘\
                                    <% } %>\
                                    <span>Version <%= requirements.php %></span>\
                                </div>\
                            </li>\
                            <li>\
                                <div class="title">Memory Limit</div>\
                                    <% if (requirements.memory_status) { %>\
                                    <div class="content success">✔︎\
                                    <% } else { %>\
                                    <div class="content fail">✘\
                                    <% } %>\
                                    <span><%= requirements.memory %></span>\
                                </div>\
                            </li>\
                            <li>\
                                <div class="title">Upload Max. Filesize</div>\
                                    <% if (requirements.upload_status) { %>\
                                    <div class="content success">✔︎\
                                    <% } else { %>\
                                    <div class="content fail">✘\
                                    <% } %>\
                                    <span><%= requirements.upload %>\
                                    <% if (!requirements.upload_status) { %>\
                                    (Recommended 64M)\
                                    <% } %>\
                                    </span>\
                                </div>\
                            </li>\
                        </ul>\
                    </div>\
                </div>\
            </div>\
            <div class="block help">\
                <div class="news-wrapper">\
                    <div class="block-title">Need Help?</div>\
                    <div class="block-content">\
                        <ul>\
                            <% json.links.forEach(function(item, i){ %>\
                            <li>\
                                <a href="<%= item.link %>"\
                                   target="_blank"\
                                   title="<%= item.title %>"><%= item.title %></a>\
                            </li>\
                            <% }) %>\
                        </ul>\
                        <% json.buttons.forEach(function(item, i){ %>\
                        <div>\
                            <a href="<%= item.link %>" \
                               class="btn"\
                               target="_blank" \
                               title="<%= item.title %>"><%= item.title %></a>\
                        </div>\
                        <% }) %>\
                    </div>\
                </div>\
            </div>\
        </div>\
        <div class="row">\
            <div class="block applications">\
                <div class="news-wrapper">\
                    <div class="block-title">Social Stream Applications</div>\
                    <div class="block-content">\
                        <div class="title"><%= json.apps.title %></div>\
                            <p class="about"><%= json.apps.description %></p>\
                            <ul class="apps">\
                                <% for(var key in json.apps.items){ %>\
                                <li>\
                                    <div class="icon">\
                                        <img src="<%= json.apps.items[key].icon %>" />\
                                    </div>\
                                    <div class="info">\
                                        <div class="name"><%= json.apps.items[key].name %></div>\
                                        <div class="description"><%= json.apps.items[key].description %></div>\
                                        <div class="links">\
                                            <% if (plugins[key].state == 0) { %>\
                                                <% json.apps.items[key].links.forEach(function(link, i){ %>\
                                                <a href="<%= link.link %>" target="_blank" class="btn" title="<%= link.title %>"><%= link.title %></a>\
                                                <% }) %>\
                                            <% } else if (plugins[key].state == 1) { %>\
                                                <a href="/wp-admin/plugins.php" target="_blank" class="btn activated" title="Installed">✔︎ Installed</a>\
                                                <a href="/wp-admin/plugins.php" target="_blank" class="btn" title="Activate">Activate</a>\
                                            <% } else if (plugins[key].state == 2){ %>\
                                                <a href="/wp-admin/admin.php?page=<%= plugins[key].plugin_page_slug %> " target="_blank" class="btn activated" title="Installed and activated">✔︎ Installed and activated</a>\
                                            <% } %>\
                                        </div>\
                                    </div>\
                                </li>\
                                <% } %>\
                            </ul>\
                        </div>\
                    </div>\
                </div>\
            </div>\
        </div>';

var ff_newstemplate_error = '\
    <div class="news-error">Something went wrong, please try to reload.</div>';


(function ($) {
    $(document).ready(function () {
        function fetchJSON() {
            return $.ajax({
                type: 'GET',
                timeout: 10000,
                url: location.protocol + '//flow.looks-awesome.com/service/news/news-1.1.json',
                data: {
                    field: 'test'
                },
                dataType: 'jsonp',
                jsonpCallback: 'grace',
                crossDomain: true
            })
        }

        function renderPage(json) {
            var $el = $('#news_page')
            var tpl = _.template(ff_newstemplate_success)
            var html = tpl({
                assets_url: FFIADMIN.assets_url,
                requirements: FFIADMIN.requirements,
                json: json,
                plugins: FFIADMIN.plugins
            })
            $el.html(html)
        }

        function renderError() {
            var $el = $('#news_page')
            var tpl = _.template(ff_newstemplate_error)
            var html = tpl({})
            $el.html(html)
        }

        var request = fetchJSON()

        request.done(function (data) {
            renderPage(data);
        }).fail(function (err) {
            renderError()
        })

    })
})(jQuery)