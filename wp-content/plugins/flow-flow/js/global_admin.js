/*!
 * JavaScript Cookie v2.0.4
 * https://github.com/js-cookie/js-cookie
 *
 * Copyright 2006, 2015 Klaus Hartl & Fagner Brack
 * Released under the MIT license
 */
(function (factory) {
  if (typeof define === 'function' && define.amd) {
    define(factory);
  } else if (typeof exports === 'object') {
    module.exports = factory();
  } else {
    /*var _OldCookies = window.Cookies;
    var api = window.Cookies = factory();
    api.noConflict = function () {
      window.Cookies = _OldCookies;
      return api;
    };*/
    window.FF_Cookie = factory();
  }
}(function () {
  function extend () {
    var i = 0;
    var result = {};
    for (; i < arguments.length; i++) {
      var attributes = arguments[ i ];
      for (var key in attributes) {
        result[key] = attributes[key];
      }
    }
    return result;
  }

  function init (converter) {
    function api (key, value, attributes) {
      var result;

      // Write

      if (arguments.length > 1) {
        attributes = extend({
          path: '/'
        }, api.defaults, attributes);

        if (typeof attributes.expires === 'number') {
          var expires = new Date();
          expires.setMilliseconds(expires.getMilliseconds() + attributes.expires * 864e+5);
          attributes.expires = expires;
        }

        try {
          result = JSON.stringify(value);
          if (/^[\{\[]/.test(result)) {
            value = result;
          }
        } catch (e) {}

        if (!converter.write) {
          value = encodeURIComponent(String(value))
            .replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g, decodeURIComponent);
        } else {
          value = converter.write(value, key);
        }

        key = encodeURIComponent(String(key));
        key = key.replace(/%(23|24|26|2B|5E|60|7C)/g, decodeURIComponent);
        key = key.replace(/[\(\)]/g, escape);

        return (document.cookie = [
          key, '=', value,
          attributes.expires && '; expires=' + attributes.expires.toUTCString(), // use expires attribute, max-age is not supported by IE
          attributes.path    && '; path=' + attributes.path,
          attributes.domain  && '; domain=' + attributes.domain,
          attributes.secure ? '; secure' : ''
        ].join(''));
      }

      // Read

      if (!key) {
        result = {};
      }

      // To prevent the for loop in the first place assign an empty array
      // in case there are no cookies at all. Also prevents odd result when
      // calling "get()"
      var cookies = document.cookie ? document.cookie.split('; ') : [];
      var rdecode = /(%[0-9A-Z]{2})+/g;
      var i = 0;

      for (; i < cookies.length; i++) {
        var parts = cookies[i].split('=');
        var name = parts[0].replace(rdecode, decodeURIComponent);
        var cookie = parts.slice(1).join('=');

        if (cookie.charAt(0) === '"') {
          cookie = cookie.slice(1, -1);
        }

        try {
          cookie = converter.read ?
            converter.read(cookie, name) : converter(cookie, name) ||
            cookie.replace(rdecode, decodeURIComponent);

          if (this.json) {
            try {
              cookie = JSON.parse(cookie);
            } catch (e) {}
          }

          if (key === name) {
            result = cookie;
            break;
          }

          if (!key) {
            result[name] = cookie;
          }
        } catch (e) {}
      }

      return result;
    }

    api.get = api.set = api;
    api.getJSON = function () {
      return api.apply({
        json: true
      }, [].slice.call(arguments));
    };
    api.defaults = {};

    api.remove = function (key, attributes) {
      api(key, '', extend(attributes, {
        expires: -1
      }));
    };

    api.withConverter = init;

    return api;
  }

  return init(function () {});
}));


// Newsticker functionality

(function($, Cookie){
  "use strict";
  if (!$) return;

  var processed = false;

  var template = '<div class="ffticker__table"><%= icon %><div><p class="ffticker__content"><span class="ffticker__title"><%= title %></span> <%= text %></p></div><div class="ffticker__cta_wrapper"><%= cta %></div></div><div class="ffticker__close"></div>';

  var TickerModel = Backbone.Model.extend({
    "date": 0,
    "title": "",
    "text": "",
    "type": "news",
    "url": ""
  });

  var TickerView = Backbone.View.extend({
    tagName: 'div',
    className: 'ffticker',
    template: _.template(template),
    initialize: function() {
//      console.log('initialize TickerView')
      this.model.view = this;
      this.render();
    },
    render: function() {
//      console.log('render campaign view')
      var self = this;
      var $name = $('#ff_company_name');

      var data = {
        icon: self.model.get('type') ? '<div class="ffticker__icon_wrapper"><span class="ffticker__icon"></span></div>' : '',
        title: ($name.length ? 'Hi' + ($name.val() ? ' ' + $name.val() : '') + '! ' : '' ) + self.model.get('title') + ':',
        text: self.model.get('text'),
        cta: self.model.get('url') ? '<a target="_blank" href="' + self.model.get('url') + '" class="ffticker__cta">' + (self.model.get('cta') ? self.model.get('cta') : 'Learn more') + '</a>' : ''
      }

      self.model.get('type') && this.$el.addClass('ffticker--' + self.model.get('type'));

      this.$el.html(this.template(data));

    },
    events: {
      "click .ffticker__close":   "destroy"
    },
    destroy: function () {
      
      this.$el.parent().removeClass('ffticker--transition-in').css({'paddingTop' : ''});
      $(document).trigger('ticker-destroyed', this.model)
    }
  });

  var TickerController = (function () {

    var view;
    var model;
    var cookieJSON = Cookie.getJSON('ff_news') || {seen: '', destroyed: ''};
    var sessionCookie = Cookie.get('ff_news_session');
    var seen, destroyed;

    function init () {

      var defer = $.Deferred();

      if (!sessionCookie || (sessionCookie && sessionCookie !== 'no_data' && sessionCookie !== 'seen' && location.href.indexOf('page=flow-flow') !== -1)) {

        setEvents();

        makeRequest().done(function (news) {

          if (news && $.isArray(news)) {
            var latest = news && news[0], found;

            if (!latest) return;
            

            Cookie.set('ff_news_session', latest.id);

            if (isSuitableToShow(latest.id)) {
              model = new TickerModel(latest);
              view = new TickerView({model: model});

              defer.resolve(view);

            } else {
              defer.reject('not suitable');
            }
          }
        }).fail(function (error) {
//          console.log(error.statusText);
          defer.reject('request failed');
        });

        if (!sessionCookie) Cookie.set('ff_news_session', 'no_data');

      } else {
        defer.reject('one time for session');
      }

      return defer.promise();
    }

    function makeRequest () {
      var defer = $.ajax({
        type: 'GET',
        url: 'http://flow.looks-awesome.com/service/news',
        data: {
          field: 'test'
        },
        dataType: 'jsonp',
        crossDomain: true
      })
      return defer.promise();
    }

    function tryToAddNotification (id) {
      var found;
      var $item;
      var str;

      seen = cookieJSON.seen.toString().split('+');

      id = parseInt(id) ? id : parseInt(Cookie.get('ff_news_session'));
      if (isNaN(id)) return;

      found = _.find(seen, function (num) {return num === id.toString()});
      if (!found) {
        $item = $('#toplevel_page_flow-flow');
        str = '<span class="update-plugins count-1"><span class="plugin-count">1</span></span>';
        $item.find('.wp-menu-name').append(str);
        $item.find('.wp-submenu li:eq(2)').prepend(str)
      }

    }

    function isSuitableToShow (id) {
      var found;

      if (cookieJSON) {
        destroyed = cookieJSON.destroyed.toString().split('+');
        found = _.find(destroyed, function (num) {return num === id.toString()});
        if (found) {
          cookieJSON.destroyed = id.toString() + '+' + cookieJSON.destroyed;
          Cookie.set('ff_news', cookieJSON, { expires: 356 });
          return false
        }
      }

      return true;
    }

    function getView () {
      return view;
    }

    function setEvents () {
      $(document).on('ticker-destroyed', function(event, model){
        var found;
        // should be in place by the moment but just in case
        if (cookieJSON) {
          destroyed = cookieJSON.destroyed.toString().split('+');
          found = _.find(destroyed, function (num) {return num === model.id.toString()});
          if (!found) {
            cookieJSON.destroyed = model.id.toString() + '+' + cookieJSON.destroyed;
            Cookie.set('ff_news', cookieJSON, { expires: 356 });
          }
        }
        //Cookie.set('ff_news', {seen: latest.id});
      })
    }

    function injectView (view) {
     
      var $form = $('#flow_flow_form');
      var height;
      var seen, found;
      if ($form.length) {

        $form.prepend(view.$el);
        height = view.$el.outerHeight() + 40;
        $form.css({'paddingTop' : height+'px'});
        setTimeout(function(){
          view.$el.parent().addClass('ffticker--transition-in');
          
        }, 0)

        // set news cookie
        seen = cookieJSON.seen.toString().split('+');
        found = _.find(seen, function (num) {return num === view.model.id.toString()});
        if (!found) {
          cookieJSON.seen = view.model.id.toString() + '+' + cookieJSON.seen;
        }
        Cookie.set('ff_news', cookieJSON, { expires: 356 });
        Cookie.set('ff_news_session', 'seen');

      }

    }

    return {
      init: init,
      getView: getView,
      injectView: injectView,
      tryToAddNotification: tryToAddNotification
    }
  })();


  $(document).one('html_ready ready', function () {
    if (processed) return false;
    processed = true;

    TickerController.init()
      .then( TickerController.injectView )
      .always( TickerController.tryToAddNotification )
      .fail( function (msg) {
//      console.log(msg)
      });

  })


})(window.jQuery, window.FF_Cookie)