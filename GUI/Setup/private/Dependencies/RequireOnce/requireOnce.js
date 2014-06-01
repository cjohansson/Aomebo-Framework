/**
 * Require Once
 *
 * Based on Ensure library by Omar AL Zabir - http://msmvps.com/blogs/omar.
 * Reworked and renamed by Christian Johansson http://cvj.se.
 *
 * @author Christian Johansson <christian@cvj.se>

 Credits:
 Global Javascript execution - <http://webreflection.blogspot.com/2007/08/global-scope-evaluation-and-dom.html>

 License:
 >Copyright (C) 2008 Omar AL Zabir - http://msmvps.com/blogs/omar
 >
 >Permission is hereby granted, free of charge,
 >to any person obtaining a copy of this software and associated
 >documentation files (the "Software"),
 >to deal in the Software without restriction,
 >including without limitation the rights to use, copy, modify, merge,
 >publish, distribute, sublicense, and/or sell copies of the Software,
 >and to permit persons to whom the Software is furnished to do so,
 >subject to the following conditions:
 >
 >The above copyright notice and this permission notice shall be included
 >in all copies or substantial portions of the Software.
 >
 >THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 >INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 >FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 >IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 >DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 >ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE
 >OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * Document Ready
 */
(function()
{
    /**
     * Require Once
     *
     * @param {Object} data
     * @param {Function} callback
     * @param {Object|Null} scope
     */
    window.requireOnce = function(data, callback, scope)
    {
        // Is not jQuery, Sys or Prototype defined?
        if (typeof(jQuery) == 'undefined'
            && typeof(Sys) == 'undefined'
            && typeof(Prototype) == 'undefined'
            ) {
            return alert('jQuery, Microsoft ASP.NET AJAX or Prototype library not found. One must be present for requireOnce to work');
        }

        // There's a test criteria which when false, the associated components must be loaded. But if true,
        // no need to load the components

        // Is test defined?
        if (typeof(data.test) != 'undefined') {

            // Define test
            var test = function() {
                return data.test
            };


            if (typeof(data.test) == 'string') {

                test = function()
                {
                    // If there's no such Javascript variable and there's no such DOM element with ID then
                    // the test fails. If any exists, then test succeeds
                    return !(eval('typeof ' + data.test) == 'undefined'
                        && document.getElementById(data.test) == null);
                }

            } else if (typeof(data.test) == 'function') {

                test = data.test;

            }

            // Now we have test prepared, time to execute the test and see if it returns null, undefined or false in any
            // scenario. If it does, then load the specified javascript/html/css
            if (test() === false
                || typeof(test()) == 'undefined'
                || test() == null
                ) {

                new RequireOnceExecutor(data, callback, scope);

                // Otherwise - Test succeeded! Just fire the callback
            } else {

                callback();

            }

            // Otherwise - no test specified
        } else {

            // No test specified. So, load necessary javascript/html/css and execute the callback
            new RequireOnceExecutor(data, callback, scope);

        }

        return null;

    };

    /**
     * Require Once Executor
     *
     * This is the main class that does the job of require-once.
     *
     * @param {Object} data
     * @param {Function} callback
     * @param {Object|Null} scope
     */
    window.RequireOnceExecutor = function(data, callback, scope)
    {

        this.data = this.clone(data);

        this.callback = (typeof(scope) == 'undefined'
            || null == scope ? callback : this.delegate(callback, scope));

        if (data.js
            && data.js.constructor != Array
            ) {
            this.data.js = [data.js];
        }

        if (data.html
            && data.html.constructor != Array
            ) {
            this.data.html = [data.html];
        }

        if (data.css
            && data.css.constructor != Array
            ) {
            this.data.css = [data.css];
        }

        if (typeof(data.js) == 'undefined') {
            this.data.js = [];
        }

        if (typeof(data.html) == 'undefined') {
            this.data.html = [];
        }

        if (typeof(data.css) == 'undefined') {
            this.data.css = [];
        }

        this.init();
        this.load();

    };

    /**
     * Require Once Executor Prototype
     */
    window.RequireOnceExecutor.prototype =
    {
        /**
         * Init
         */
        init : function()
        {
            // Fetch Javascript using Framework specific library
            if (typeof(jQuery) != 'undefined') {

                this.getJS = HttpLibrary.loadJavascript_jQuery;
                this.httpGet = HttpLibrary.httpGet_jQuery;

                // Otherwise - is Prototype available?
            } else if (typeof(Prototype) != 'undefined') {

                this.getJS = HttpLibrary.loadJavascript_Prototype;
                this.httpGet = HttpLibrary.httpGet_Prototype;

                // Otherwise - is Sys available?
            } else if (typeof(Sys) != 'undefined') {

                this.getJS = HttpLibrary.loadJavascript_MSAJAX;
                this.httpGet = HttpLibrary.httpGet_MSAJAX;

                // Otherwise - no javascript framework found
            } else {

                throw 'jQuery, Prototype or MS AJAX framework not found';

            }
        },
        /**
         * Get Js
         *
         * @param {Object} data
         */
        getJS : function(data)
        {
            // abstract function to get Javascript and execute it
        },

        /**
         * Http Get
         *
         * @param {String} url
         * @param {Function} callback
         */
        httpGet : function(url, callback)
        {
            // abstract function to make HTTP GET call
        },

        /**
         * Load
         */
        load : function()
        {
            this.loadJavascripts(this.delegate(function()
            {
                this.loadCSS(this.delegate(function()
                {
                    this.loadHtml(this.delegate(function()
                    {
                        this.callback()
                    }))
                }))
            }));
        },

        /**
         * Load Java-Scripts
         *
         * @param {Function} complete
         */
        loadJavascripts: function(complete)
        {
            var scriptsToLoad = this.data.js.length;

            if (scriptsToLoad === 0) {
                return complete();
            }

            this.forEach(this.data.js, function(href)
            {
                if (HttpLibrary.isUrlLoaded(href)
                    || this.isTagLoaded('script', 'src', href)
                    ) {

                    scriptsToLoad --;

                } else {

                    this.getJS({
                        url: href,
                        success: this.delegate(function()
                        {
                            if (!HttpLibrary.isUrlLoaded(href)) {
                                scriptsToLoad--;
                                HttpLibrary.registerUrl(href);
                            }
                        }),
                        error: this.delegate(function(msg)
                        {
                            scriptsToLoad --;
                            if(typeof this.data.error == "function") this.data.error(href, msg);
                        })
                    });

                }
            });

            // wait until all the external scripts are downloaded
            this.until({
                test: function() { return scriptsToLoad <= 0; },
                delay: 50,
                callback: this.delegate(function()
                {
                    complete();
                })
            });

            return null;

        },
        loadCSS : function(complete)
        {

            if (this.data.css.length === 0) {
                return complete();
            }

            var head = HttpLibrary.getHead();
            this.forEach(this.data.css, function(href)
            {
                if (HttpLibrary.isUrlLoaded(href)
                    || this.isTagLoaded('link', 'href', href)
                    ) {
                    // Do nothing
                } else {

                    var self = this;
                    try
                    {
                        (function(href, head)
                        {
                            var link = document.createElement('link');
                            link.setAttribute('href', href);
                            link.setAttribute('rel', 'stylesheet');
                            link.setAttribute('type', 'text/css');
                            head.appendChild(link);

                            HttpLibrary.registerUrl(href);
                        }).apply(window, [href, head]);
                    }
                    catch(e)
                    {
                        if (typeof(self.data.error) == 'function') {
                            self.data.error(href, e.message);
                        }
                    }

                }
            });

            complete();

            return null;
        },

        /**
         * Load Html
         *
         * @param {Function} complete
         */
        loadHtml: function(complete)
        {

            var htmlToDownload = this.data.html.length;

            if (htmlToDownload === 0) {
                return complete();
            }

            this.forEach(this.data.html, function(href)
            {
                if (HttpLibrary.isUrlLoaded(href)) {
                    htmlToDownload --;
                } else {
                    this.httpGet({
                        url:        href,
                        success:    this.delegate(function(content)
                        {
                            htmlToDownload --;
                            HttpLibrary.registerUrl(href);

                            var parent = (this.data.parent || document.body.appendChild(document.createElement("div")));
                            if (typeof(parent) == 'string') {
                                parent = document.getElementById(parent);
                            }

                            parent.innerHTML = content;

                        }),
                        error:      this.delegate(function(msg)
                        {
                            htmlToDownload --;
                            if(typeof this.data.error == "function") this.data.error(href, msg);
                        })
                    });
                }
            });

            // wait until all the external scripts are downloaded
            this.until({
                test:       function() { return htmlToDownload === 0; },
                delay:      50,
                callback:   this.delegate(function()
                {
                    complete();
                })
            });

            return null;

        },

        /**
         * Clone
         *
         * @param {Object} obj
         */
        clone : function(obj)
        {
            var cloned = {};
            for (var p in obj)
            {
                if (obj.hasOwnProperty(p)) {

                    var x = obj[p];

                    if (typeof(x) == 'object') {

                        if (x.constructor == Array) {
                            var a = [];
                            for( var i = 0; i < x.length; i ++ ) a.push(x[i]);
                            cloned[p] = a;

                        } else {
                            cloned[p] = this.clone(x);
                        }
                    } else {
                        cloned[p] = x;
                    }

                }
            }

            return cloned;
        },

        /**
         * For Each
         *
         * @param {NodeList} arr
         * @param {Function} callback
         */
        forEach: function(arr, callback)
        {
            var self = this;
            for( var i = 0; i < arr.length; i ++ )
                callback.apply(self, [arr[i]]);
        },

        /**
         * Delegate
         *
         * @param {Function} func
         * @param {Object|Null} [obj = Null]
         */
        delegate: function(func, obj)
        {
            var context = obj || this;
            return function() { func.apply(context, arguments); }
        },

        /**
         * Until
         *
         * @param {Object} o
         */
        until: function(o /* o = { test: function(){...}, delay:100, callback: function(){...} } */)
        {
            if (o.test() === true) {
                o.callback();
            } else {
                window.setTimeout( this.delegate( function() { this.until(o); } ), o.delay || 50);
            }
        },

        /**
         * Is Tag Loaded
         *
         * @param {String} tagName
         * @param {String} attName
         * @param {String} value
         */
        isTagLoaded: function(tagName, attName, value)
        {
            // Create a temporary tag to see what value browser eventually
            // gives to the attribute after doing necessary encoding
            var tag = document.createElement(tagName);
            tag[attName] = value;
            var tagFound = false;
            var tags = document.getElementsByTagName(tagName);
            this.forEach(tags, function(t)
            {
                if (tag[attName] === t[attName]) {
                    tagFound = true;
                }
                return false;
            });
            return tagFound;
        }
    };

    /**
     * User-Agent
     *
     * @var {String}
     */
    var userAgent = navigator.userAgent.toLowerCase();

    /**
     * Http Library
     *
     * HttpLibrary is a cross browser, cross framework library to perform common operations
     * like HTTP GET, injecting script into DOM, keeping track of loaded url etc. It provides
     * implementations for various frameworks including jQuery, MSAJAX or Prototype.
     *
     * @var {Object}
     */
    var HttpLibrary =
    {

        /**
         * Browser
         *
         * @var {Object}
         */
        browser : {
            version: (userAgent.match( /.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/ ) || [])[1],
            safari: /webkit/.test( userAgent ),
            opera: /opera/.test( userAgent ),
            msie: /msie/.test( userAgent ) && !/opera/.test( userAgent ),
            mozilla: /mozilla/.test( userAgent ) && !/(compatible|webkit)/.test( userAgent )
        },
        /**
         * Loaded Urls
         *
         * @var {Object}
         */
        loadedUrls : {},

        /**
         * Is Url Loaded
         *
         * @param {String} url
         * @return {Boolean}
         */
        isUrlLoaded : function(url)
        {
            return HttpLibrary.loadedUrls[url] === true;
        },

        /***
         * Register-Url
         *
         * @param {String} url
         */
        registerUrl : function(url)
        {
            HttpLibrary.loadedUrls[url] = true;
        },

        /**
         * Create Script Tag
         *
         * @param {String} url
         * @param {Function} success
         * @param {Function} error
         */
        createScriptTag : function(url, success, error)
        {

            var script = document.createElement('script');

            script.type = 'text/javascript';
            script.async = true;
            script.charset = 'utf-8';

            // Is attachEvent function available?
            if (script.attachEvent
                && !(script.attachEvent.toString
                && script.attachEvent.toString().indexOf('[native code') < 0)
                ) {

                /**
                 * When Ready-State Changes..
                 */
                script.onreadystatechange = function()
                {
                    HttpLibrary.readyStateLoaded(this, success);
                };

                /**
                 * When Script Loads..
                 */
                script.onload = function()
                {
                    HttpLibrary.readyStateLoaded(this, success);
                };

                /**
                 * When Script Causes Error..
                 */
                script.onerror = function()
                {
                    error(data.url + ' failed to load');
                };

                // Otherwise - use eventListeners instead
            } else {

                /**
                 * When Script Loads..
                 */
                script.addEventListener('load', function(event)
                {
                    HttpLibrary.readyStateLoaded(event, success);
                }, false);

                /**
                 * When Script Causes Error..
                 */
                script.addEventListener('error', function()
                {
                    error(data.url + ' failed to load');
                }, false);

            }

            script.src = url;

            var s = document.getElementsByTagName('head')[0];
            s.insertBefore(script, s.firstChild);

        },

        /**
         * Get Head
         *
         * @return {Node}
         */
        getHead : function()
        {
            return document.getElementsByTagName('head')[0] || document.documentElement
        },

        /**
         * Load Java-Script Jquery
         *
         * @param {String} data
         */
        loadJavascript_jQuery : function(data)
        {
            HttpLibrary.createScriptTag(data.url, data.success, data.error);
        },

        /**
         * Load Java-Script Msajax
         *
         * @param {String} data
         */
        loadJavascript_MSAJAX : function(data)
        {
            HttpLibrary.createScriptTag(data.url, data.success, data.error);
        },

        /**
         * Load Java-Script Prototype
         *
         * @param {String} data
         */
        loadJavascript_Prototype : function(data)
        {

            HttpLibrary.createScriptTag(data.url, data.success, data.error);
        },

        /**
         * Http-Get Jquery
         *
         * @param {String} data
         */
        httpGet_jQuery : function(data)
        {
            return jQuery.ajax({
                type:       "GET",
                url:        data.url,
                data:       null,
                success:    data.success,
                error:      function(xml, status, e)
                {
                    if( xml && xml.responseText )
                        data.error(xml.responseText);
                    else
                        data.error("Error occured while loading: " + url +'\n' + e.message);
                },
                dataType: data.type || "html"
            });
        },

        /**
         * Http-Get Msajax
         *
         * @param {String} data
         */
        httpGet_MSAJAX : function(data)
        {
            var _wRequest =  new Sys.Net.WebRequest();
            _wRequest.set_url(data.url);
            _wRequest.set_httpVerb("GET");
            _wRequest.add_completed(function (result)
            {
                var errorMsg = "Failed to load:" + data.url;
                if (result.get_timedOut()) {
                    errorMsg = "Timed out";
                }
                if (result.get_aborted()) {
                    errorMsg = "Aborted";
                }

                if (result.get_responseAvailable()) data.success( result.get_responseData() );
                else data.error( errorMsg );
            });

            var executor = new Sys.Net.XMLHttpExecutor();
            _wRequest.set_executor(executor);
            executor.executeRequest();
        },

        /**
         * Http-Get Prototype
         *
         * @param {String} data
         */
        httpGet_Prototype : function(data)
        {
            new Ajax.Request(data.url, {
                method:     'get',
                evalJS:     false,  // Make sure prototype does not automatically evan scripts
                onSuccess:  function(transport, json)
                {
                    data.success(transport.responseText || "");
                },
                onFailure : data.error
            });
        },

        /**
         * Ready-State Loaded
         *
         * @param {Object} event
         * @param {Function} callback
         */
        readyStateLoaded: function(event, callback)
        {

            if (typeof(event) != 'undefined') {

                if (typeof(event.type) != 'undefined') {
                    if (event.type == 'load') {
                        callback();
                    }
                }

                if (typeof(event.readyState) != 'undefined') {
                    if (event.readyState == 'complete'
                        || event.readyState == 'loaded'
                        ) {
                        callback();
                    }

                } else if (typeof(event.currentTarget) != 'undefined') {

                    if ((event.currentTarget).readyState == 'complete'
                        || (event.currentTarget).readyState == 'loaded'
                        ) {
                        callback();
                    }

                } else if (typeof(event.srcElement) != 'undefined') {

                    if ((event.srcElement).readyState == 'complete'
                        || (event.srcElement).readyState == 'loaded'
                        ) {
                        callback();
                    }

                }
            }

        }
    };

})();
