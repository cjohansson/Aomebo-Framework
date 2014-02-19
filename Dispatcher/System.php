<?php
/**
 * Aomebo - a module-based MVC framework for PHP 5.3+
 *
 * Copyright (C) 2010+ Christian Johansson <christian@cvj.se>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 * @license LGPL version 3
 * @see http://www.aomebo.org
 */

/**
 *
 */
namespace Aomebo\Dispatcher
{

    /**
     * @method static \Aomebo\Dispatcher\System getInstance()
     */
    class System extends \Aomebo\Singleton
    {

        /**
         * Key-name specifying what env variable indicates
         * that apache mod_rewrite is on.
         *
         * @var string
         */
        const REWRITE_FLAG = 'REWRITE_ENABLED';

        /**
         * Key-name indicating http request.
         *
         * @var string
         */
        const SERVER_PROTOCOL_HTTP = 'server_protocol_http';

        /**
         * Key-name indicating https request.
         *
         * @var string
         */
        const SERVER_PROTOCOL_HTTPS = 'server_protocol_https';

        /**
         * Requests a representation of the specified resource.
         * Requests using GET should only retrieve data and
         * should have no other effect. (This is also true of some
         * other HTTP methods.)[1] The W3C has published
         * guidance principles on this distinction, saying,
         * "Web application design should be informed by
         * the above principles, but also by the relevant
         * limitations."[11] See safe methods below.
         *
         * @see http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol
         * @var string
         */
        const HTTP_REQUEST_TYPE_GET = 'GET';

        /**
         * Requests that the server accept the entity enclosed in the
         * request as a new subordinate of the web resource identified
         * by the URI. The data POSTed might be, as examples, an
         * annotation for existing resources; a message for a
         * bulletin board, newsgroup, mailing list, or comment
         * thread; a block of data that is the result of
         * submitting a web form to a data-handling process;
         * or an item to add to a database.[12]
         *
         * @see http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol
         * @var string
         */
        const HTTP_REQUEST_TYPE_POST = 'POST';

        /**
         * Asks for the response identical to the one that would correspond
         * to a GET request, but without the response body. This is useful
         * for retrieving meta-information written in response headers,
         * without having to transport the entire content.
         *
         * @see http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol
         * @var string
         */
        const HTTP_REQUEST_TYPE_HEAD = 'HEAD';

        /**
         * Returns the HTTP methods that the server supports for
         * specified URL. This can be used to check the functionality
         * of a web server by requesting '*' instead of a specific resource.
         *
         * @see http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol
         * @var string
         */
        const HTTP_REQUEST_TYPE_OPTIONS = 'OPTIONS';

        /**
         * Requests that the enclosed entity be stored under the
         * supplied URI. If the URI refers to an already existing
         * resource, it is modified; if the URI does not point to an
         * existing resource, then the server can
         * create the resource with that URI.[13]
         *
         * @see http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol
         * @var string
         */
        const HTTP_REQUEST_TYPE_PUT = 'PUT';

        /**
         * Deletes the specified resource.
         *
         * @see http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol
         * @var string
         */
        const HTTP_REQUEST_TYPE_DELETE = 'DELETE';

        /**
         * Echoes back the received request so that a
         * client can see what (if any) changes or additions have been
         * made by intermediate servers.
         *
         * @see http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol
         * @var string
         */
        const HTTP_REQUEST_TYPE_TRACE = 'TRACE';

        /**
         * Converts the request connection to a transparent TCP/IP tunnel,
         * usually to facilitate SSL-encrypted communication (HTTPS) through
         * an unencrypted HTTP proxy.[14][15]
         *
         * @see http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol
         * @var string
         */
        const HTTP_REQUEST_TYPE_CONNECT = 'CONNECT';

        /**
         * Is used to apply partial modifications to a resource.[16]
         *
         * @see http://en.wikipedia.org/wiki/Hypertext_Transfer_Protocol
         * @var string
         */
        const HTTP_REQUEST_TYPE_PATCH = 'PATCH';

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_page;

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_fullRequest;

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_requestUri;

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_queryString;

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_rewriteEnabled;

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_serverProtocol;

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_serverProtocolVersion;

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_fileNotFoundFlag;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_routes = array();

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_baseUri;

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_pageBaseUri;

        /**
         * @internal
         * @static
         * @var array
         */
        private static $_httpHeaderFields = array();

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_httpResponseStatus = '';

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_outputtedHeaders = false;

        /**
         * @internal
         * @static
         * @var bool|null
         */
        private static $_shellOutputHeaders;

        /**
         * @internal
         * @static
         * @var string|null
         */
        private static $_httpRequestMethod;

        /**
         * @internal
         * @static
         * @var bool
         */
        private static $_isRedirecting = false;

        /**
         * @internal
         * @static
         * @var string
         */
        private static $_pageSyntaxRegexp = '';

        /**
         *
         */
        public function __construct()
        {
            parent::__construct();
            if (!$this->_isConstructed()) {

                \Aomebo\Trigger\System::processTriggers(
                    \Aomebo\Trigger\System::TRIGGER_KEY_BEFORE_DISPATCH);

                if (!isset(self::$_rewriteEnabled)) {
                    $this->_parseServer();
                }

                if (!isset(self::$_serverProtocol)
                    || !isset(self::$_serverProtocolVersion)
                ) {
                    $this->_parseProtocol();
                }

                if (!isset(self::$_requestUri)
                    || !isset(self::$_fullRequest)
                    || !isset(self::$_queryString)
                    || !isset(self::$_baseUri)
                    || !isset(self::$_pageBaseUri)
                    || !isset(self::$_httpRequestMethod)
                ) {
                    $this->_parseRequest();
                }

                if (!isset(self::$_page)
                    && !$this->isAjaxRequest()
                ) {
                    $this->_parsePage();
                }

                \Aomebo\Trigger\System::processTriggers(
                    \Aomebo\Trigger\System::TRIGGER_KEY_AFTER_DISPATCH);

                $this->_flagThisConstructed();

            }
        }

        /**
         *
         */
        public function __destruct()
        {
            self::outputHttpHeaders();
        }

        /**
         * @static
         * @return string
         */
        public static function getPageSyntaxRegexp()
        {
            if (empty(self::$_pageSyntaxRegexp)) {
                self::$_pageSyntaxRegexp =
                    \Aomebo\Configuration::getSetting(
                        'dispatch,page syntax regexp');
            }
            return self::$_pageSyntaxRegexp;
        }

        /**
         * @static
         * @return string
         */
        public static function getPage()
        {
            return self::$_page;
        }

        /**
         * @static
         * @param string $page
         */
        public static function setPage($page)
        {
            self::$_page = $page;
        }

        /**
         * @param string $pageBaseUri
         */
        public static function setPageBaseUri($pageBaseUri)
        {
            self::$_pageBaseUri = $pageBaseUri;
        }

        /**
         * @static
         * @return string
         */
        public static function getPageBaseUri()
        {
            return self::$_pageBaseUri;
        }

        /**
         * @static
         * @param string $baseUri
         */
        public static function setBaseUri($baseUri)
        {
            self::$_baseUri = $baseUri;
        }

        /**
         * @static
         * @return string
         */
        public static function getBaseUri()
        {
            return self::$_baseUri;
        }

        /**
         * @static
         * @return string
         */
        public static function getFullRequest()
        {
            return self::$_fullRequest;
        }

        /**
         * @static
         * @param bool $flag
         */
        public static function setFileNotFoundFlag($flag)
        {
            self::$_fileNotFoundFlag = (!empty($flag));
        }

        /**
         * @static
         * @param string $name
         * @return bool
         */
        public static function hasSuccessfullFileUpload($name)
        {
            if (!empty($name)
                && is_string($name)
            ) {
                if (self::hasFileUploads()) {
                    if (isset($_FILES[$name], $_FILES[$name]['error'])
                        && !empty($_FILES[$name]['name'])
                        && !empty($_FILES[$name]['tmp_name'])
                        && !empty($_FILES[$name]['size'])
                        && $_FILES[$name]['error'] == UPLOAD_ERR_OK
                    ) {
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * @static
         * @return bool
         */
        public static function hasFileUploads()
        {
            return (isset($_FILES)
                && is_array($_FILES)
                && sizeof($_FILES) > 0);
        }

        /**
         * @static
         * @return string|bool
         */
        public static function getCurrentAjaxPageData()
        {

            $allowAjaxPostRequests =
                \Aomebo\Configuration::getSetting('dispatch,allow ajax post requests');
            $allowAjaxGetRequests =
                \Aomebo\Configuration::getSetting('dispatch,allow ajax get requests');

            if ($allowAjaxPostRequests
                && self::isHttpPostRequest()
                && (isset($_POST['page'])
                || isset($_POST['_page']))
            ) {
                if (isset($_POST['page'])) {
                    return $_POST['page'];
                } else if (isset($_POST['_page'])) {
                    return $_POST['_page'];
                }
            } else if ($allowAjaxGetRequests
                && self::isHttpGetRequest()
                && (isset($_GET['page'])
                || isset($_GET['_page']))
            ) {
                if (isset($_GET['page'])) {
                    return $_GET['page'];
                } else if (isset($_GET['_page'])) {
                    return $_GET['_page'];
                }
            }

            return false;

        }

        /**
         * This method determins whether current request
         * is a request for associatives.
         *
         * @static
         * @return bool
         */
        public static function isAssociativesRequest()
        {
            if (isset($_GET['mode'])) {
                if ($_GET['mode'] ==
                    \Aomebo\Configuration::getSetting('settings,associatives mode')
                ) {
                    return true;
                }
            }
            return false;
        }

        /**
         * This method returns whether current request is an
         * ordinary http request.
         *
         * That is all requests which are not called from shell
         * environment.
         *
         * @static
         * @return bool
         */
        public static function isHttpRequest()
        {
            return (!self::isShellRequest());
        }

        /**
         * This method returns if current request is a normal request,
         * that is a page request which is not ajax.
         *
         * @static
         * @return bool
         */
        public static function isNormalRequest()
        {
            return (self::isPageRequest()
                && !self::isAjaxRequest());
        }

        /**
         * This method returns whether current request
         * is an ordinary page request.
         *
         * That is a normal request or a ajax request.
         *
         * @static
         * @return bool
         */
        public static function isPageRequest()
        {
            return (!self::isShellRequest()
                && !self::isFaviconRequest()
                && !self::isAssociativesRequest());
        }

        /**
         * This method returns whether current request is from shell or not.
         *
         * @static
         * @return bool
         */
        public static function isShellRequest()
        {
            return !empty($_SERVER['SHELL']);
        }

        /**
         * @static
         * @return bool
         */
        public static function isFaviconRequest()
        {
            return (\Aomebo\Configuration::getSetting(
                'output,favicon directs to site shortcut icon')
                && self::$_fullRequest == 'favicon.ico');
        }

        /**
         * This method returns all shell arguments.
         *
         * @static
         * @return array|bool
         */
        public static function getShellArguments()
        {
            if (isset($_SERVER['argv'])
                && is_array($_SERVER['argv'])
                && sizeof($_SERVER['argv']) > 0
            ) {
                return $_SERVER['argv'];
            } else {
                return false;
            }
        }

        /**
         * @static
         * @return bool|string
         */
        public static function getHttpResponseStatus()
        {
            if (!empty(self::$_httpResponseStatus)) {
                return self::$_httpResponseStatus;
            }
            return false;
        }

        /**
         * @static
         * @return array|bool
         */
        public static function getHttpHeaderFields()
        {
            if (isset(self::$_httpHeaderFields)
                && is_array(self::$_httpHeaderFields)
                && sizeof(self::$_httpHeaderFields) > 0
            ) {
                return self::$_httpHeaderFields;
            }
            return false;
        }

        /**
         * @static
         * @see https://en.wikipedia.org/wiki/List_of_HTTP_header_fields
         */
        public static function outputHttpHeaders()
        {
            if (!self::hasOutputtedHeaders()) {

                // HTTP status header is managed automatically if in apache environment
                if (!self::getHttpResponseStatus()
                    && self::isShellRequest()
                ) {
                    self::setHttpResponseStatus200Ok();
                }

                // HTTP Header response status
                if (self::getHttpResponseStatus()) {
                    self::setHeader(
                        self::getServerProtocolString() . ' '
                        . self::$_httpResponseStatus);
                }

                // HTTP Header fields
                if ($headerFields = self::getHttpHeaderFields()) {
                    foreach ($headerFields as $key => $value)
                    {
                        self::setHeader($key . ': ' . $value);
                    }
                }

                // HTTP End of headers
                self::setEndOfHeaders();
                self::setHasOutputtedHeaders(true);

            }
        }

        /**
         * @static
         * @return bool
         */
        public static function hasOutputtedHeaders()
        {
            return (!empty(self::$_outputtedHeaders));
        }

        /**
         * @param bool $flag
         */
        public static function setHasOutputtedHeaders($flag)
        {
            self::$_outputtedHeaders = (!empty($flag));
        }

        /**
         * This method returns the uri for ajax requests.
         *
         * @static
         * @return string
         */
        public static function getAjaxUri()
        {
            if (self::$_rewriteEnabled) {
                return self::$_pageBaseUri
                    . \Aomebo\Configuration::getSetting('settings,ajax mode');
            } else {
                return self::$_pageBaseUri . 'index.php?mode='
                    . \Aomebo\Configuration::getSetting('settings,ajax mode');
            }
        }

        /**
         * This method determins whether current request
         * is a aomebo ajax request.
         *
         * @static
         * @return bool
         */
        public static function isAjaxRequest()
        {

            $allowAjaxPostRequests =
                \Aomebo\Configuration::getSetting('dispatch,allow ajax post requests');
            $allowAjaxGetRequests =
                \Aomebo\Configuration::getSetting('dispatch,allow ajax get requests');
            $ajaxMode =
                \Aomebo\Configuration::getSetting('settings,ajax mode');

            if (isset($_GET['mode'])
                && $_GET['mode'] == $ajaxMode
                && (($allowAjaxPostRequests
                    && self::isHttpPostRequest()
                    && (!empty($_POST['page']))
                    || !empty($_POST['_page']))
                || ($allowAjaxGetRequests
                    && self::isHttpGetRequest()
                    && (!empty($_GET['page'])
                    || !empty($_GET['_page']))))
            ) {
                return true;
            } else {
                return false;
            }

        }

        /**
         * This method stops interpretating and
         * changes pages to file-not-found.
         *
         * @static
         * @param bool [$restartInterpretation = true]
         */
        public static function fileNotFound($restartInterpretation = true)
        {

            self::setPage(
                \Aomebo\Configuration::getSetting('dispatch,file not found page'));
            self::setFileNotFoundFlag(true);

            if ($restartInterpretation) {
                $interpreterEngine =
                    \Aomebo\Interpreter\Engine::getInstance();
                $interpreterEngine->restartInterpretation();
            }

            self::removeCurrentUriFromIndexing();
            self::setHttpResponseStatus404NotFound();

            // Should we redirect to 404 page?
            if (\Aomebo\Configuration::getSetting(
                'dispatch,redirect to file not found page')
            ) {
                self::setHttpHeaderField('Location',
                    self::buildUri(null, self::getPage()));
            }

        }

        /**
         * @static
         */
        public static function removeCurrentUriFromIndexing()
        {
            // Do we have a request-uri?
            if (!empty(self::$_fullRequest)) {
                $indexingEngine =
                    \Aomebo\Indexing\Engine::getInstance();
                $indexingEngine->removeUri(self::$_fullRequest);
                $indexingEngine->disallowIndexing();
            }
        }

        /**
         * @static
         * @return string
         */
        public static function getServerProtocolString()
        {
            $protocolString = '';
            if (self::$_serverProtocol == self::SERVER_PROTOCOL_HTTP) {
                $protocolString .= 'HTTP';
            } else if (self::$_serverProtocol == self::SERVER_PROTOCOL_HTTPS) {
                $protocolString .= 'HTTPS';
            }
            if (isset(self::$_serverProtocolVersion)) {
                $protocolString .= '/'
                    . number_format(self::$_serverProtocolVersion, 1);
            }
            return $protocolString;
        }

        /**
         * A corresponding 3xx, 201 or 202 should be
         * specified before using location.
         *
         * @static
         * @param string $location      Should be an absolute URI
         * @see http://en.wikipedia.org/wiki/HTTP_location
         */
        public static function setHeaderLocation($location)
        {
            self::setHttpHeaderField(
                'Location',
                $location);
        }

        /**
         * @static
         * @param string $location
         */
        public static function setHttpHeaderFieldLocation($location)
        {
            self::setHttpHeaderField('Location', $location);
            self::setIsRedirecting(true);
        }

        /**
         * @static
         * @param bool $redirecting
         */
        public static function setIsRedirecting($redirecting)
        {
            self::$_isRedirecting = (!empty($redirecting));
        }

        /**
         * @static
         * @return bool
         */
        public static function isRedirecting()
        {
            return (!empty(self::$_isRedirecting));
        }

        /**
         * @static
         * @param string $status
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus($status)
        {
            self::$_httpResponseStatus = $status;
        }

        /**
         * Standard response for successful HTTP requests. The actual
         * response will depend on the request method used. In a GET request,
         * the response will contain an entity corresponding to the requested
         * resource. In a POST request the response will contain an entity
         * describing or containing the result of the action.[2]
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus200Ok()
        {
            self::setHttpResponseStatus('200 OK');
        }

        /**
         * The request has been fulfilled and resulted in a new resource being created.
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus201Created()
        {
            self::setHttpResponseStatus('201 Created');
        }

        /**
         * The request has been accepted for processing, but the processing
         * has not been completed. The request might or might not eventually
         * be acted upon, as it might be disallowed when processing actually takes place.
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus202Accepted()
        {
            self::setHttpResponseStatus('202 Accepted');
        }

        /**
         * Indicates multiple options for the resource that the
         * client may follow. It, for instance, could be used to
         * present different format options for video, list files with
         * different extensions, or word sense disambiguation.[2]
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus300MultipleChoices()
        {
            self::setHttpResponseStatus('300 Multiple Choices');
        }

        /**
         * This and all future requests should be directed to the given URI.
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus301MovedPermanently()
        {
            self::setHttpResponseStatus('301 Moved Permanently');
        }

        /**
         * This is an example of industry practice contradicting the standard.[2]
         * The HTTP/1.0 specification (RFC 1945) required the client to perform
         * a temporary redirect (the original describing phrase was
         * "Moved Temporarily"),[6] but popular browsers implemented 302
         * with the functionality of a 303 See Other. Therefore, HTTP/1.1 added
         * status codes 303 and 307 to distinguish between the two behaviours.[7]
         * However, some Web applications and frameworks use the 302 status code as
         * if it were the 303.[8]
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus302Found()
        {
            self::setHttpResponseStatus('302 Found');
        }

        /**
         * The response to the request can be found under another URI
         * using a GET method. When received in response to a POST (or PUT/DELETE),
         * it should be assumed that the server has received the data and the
         * redirect should be issued with a separate GET message.
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus303SeeOther()
        {
            self::setHttpResponseStatus('303 See Other');
        }

        /**
         * Indicates that the resource has not been modified since the
         * version specified by the request headers If-Modified-Since
         * or If-Match.[2] This means that there is no need to retransmit
         * the resource, since the client still has a previously-downloaded copy.
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
         */
        public static function setHttpResponseStatus304NotModified()
        {
            self::setHttpResponseStatus('304 Not Modified');
        }

        /**
         * The requested resource is only available through a proxy,
         * whose address is provided in the response.[2]
         * Many HTTP clients (such as Mozilla[9] and Internet Explorer)
         * do not correctly handle responses with this status code,
         * primarily for security reasons.
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus305UseProxy()
        {
            self::setHttpResponseStatus('305 Use Proxy');
        }

        /**
         * No longer used.[2] Originally meant
         * "Subsequent requests should use the specified proxy."[10]
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus306SwitchProxy()
        {
            self::setHttpResponseStatus('306 Switch Proxy');
        }

        /**
         * In this case, the request should be repeated with another
         * URI; however, future requests should still use the
         * original URI.[2] In contrast to how 302 was historically
         * implemented, the request method is not allowed to be
         * changed when reissuing the original request.
         * For instance, a POST request should be repeated
         * using another POST request.[11]
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus307TemporaryRedirect()
        {
            self::setHttpResponseStatus('307 Temporary Redirect');
        }

        /**
         * The request, and all future requests should be repeated using
         * another URI. 307 and 308 (as proposed) parallel the behaviours
         * of 302 and 301, but do not allow the HTTP method to change.
         * So, for example, submitting a form to a permanently
         * redirected resource may continue smoothly.
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus308PermanentRedirect()
        {
            self::setHttpResponseStatus('308 Permanent Redirect');
        }

        /**
         * The request cannot be fulfilled due to bad syntax.[2]
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus400BadRequest()
        {
            self::setHttpResponseStatus('400 Bad Request');
        }

        /**
         * Similar to 403 Forbidden, but specifically for use when
         * authentication is required and has failed or has not yet
         * been provided.[2] The response must include a
         * WWW-Authenticate header field containing a challenge applicable
         * to the requested resource. See Basic access authentication
         * and Digest access authentication.
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus401Unauthorized()
        {
            self::setHttpResponseStatus('401 Unauthorized');
        }

        /**
         * The request was a valid request, but the server is refusing to respond to it.
         * [2] Unlike a 401 Unauthorized response, authenticating will make no
         * difference.[2] On servers where authentication is required, this commonly
         * means that the provided credentials were successfully authenticated but
         * that the credentials still do not grant the client permission to access
         * the resource (e.g. a recognized user attempting to access restricted content).
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus403Forbidden()
        {
            self::setHttpResponseStatus('403 Forbidden');
        }

        /**
         * The requested resource could not be found but may be available
         * again in the future.[2] Subsequent requests by the client are permissible.
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus404NotFound()
        {
            self::setHttpResponseStatus('404 Not Found');
        }

        /**
         * A generic error message, given when no more specific message is suitable.
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus500InternalServerError()
        {
            self::setHttpResponseStatus('500 Internal Server Error');
        }

        /**
         * The server either does not recognize the request method, or it
         * lacks the ability to fulfill the request.[2] Usually this implies
         * future availability (eg. a new feature of a web-service API).
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus501NotImplemented()
        {
            self::setHttpResponseStatus('501 Not Implemented');
        }

        /**
         * The server is currently unavailable (because it is overloaded or
         * down for maintenance).[2] Generally, this is a temporary state.
         *
         * @static
         * @see http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
         */
        public static function setHttpResponseStatus503ServiceUnavailable()
        {
            self::setHttpResponseStatus('503 Service Unavailable');
        }

        /**
         * @static
         * @param string $field
         * @param string $value
         * @see https://en.wikipedia.org/wiki/List_of_HTTP_header_fields
         */
        public static function setHttpHeaderField($field, $value)
        {
            self::$_httpHeaderFields[ucfirst($field)] = $value;
        }

        /**
         * @static
         * @param string $header
         * @see https://en.wikipedia.org/wiki/List_of_HTTP_header_fields
         */
        public static function setHeader($header)
        {
            if (self::isShellRequest()) {
                if (!isset(self::$_shellOutputHeaders)) {
                    self::$_shellOutputHeaders =
                        \Aomebo\Configuration::getSetting('output,output headers in shell mode');
                }
                if (self::$_shellOutputHeaders) {
                    echo $header . "\r\n";
                }
            } else {
                header($header, true);
            }
        }

        /**
         * @static
         * @see https://en.wikipedia.org/wiki/List_of_HTTP_header_fields
         */
        public static function setEndOfHeaders()
        {
            if (self::isShellRequest()) {
                if (!isset(self::$_shellOutputHeaders)) {
                    self::$_shellOutputHeaders =
                        \Aomebo\Configuration::getSetting('output,output headers in shell mode');
                }
                if (self::$_shellOutputHeaders) {
                    echo "\r\n";
                }
            }
        }

        /**
         * @static
         * @return string
         */
        public static function getQueryString()
        {
            return (!empty(self::$_queryString) ? self::$_queryString : '');
        }

        /**
         * @static
         * @return bool
         */
        public static function getFileNotFoundFlag()
        {
            return (!empty(self::$_fileNotFoundFlag) ? true : false);
        }

        /**
         * @static
         * @param string $requestMethod
         */
        public static function setHttpRequestMethod($requestMethod)
        {
            if (!empty($requestMethod)) {
                self::$_httpRequestMethod = (string) $requestMethod;
            }
        }

        /**
         * @static
         * @return bool
         */
        public static function isHttpGetRequest()
        {
            return self::isHttpRequestMethod(self::HTTP_REQUEST_TYPE_GET);
        }

        /**
         * @static
         * @return bool
         */
        public static function isHttpPostRequest()
        {
            return self::isHttpRequestMethod(self::HTTP_REQUEST_TYPE_POST);
        }

        /**
         * @static
         * @return bool
         */
        public static function isHttpPostRequestWithPostData()
        {
            return (self::isHttpPostRequest()
                && isset($_POST)
                && is_array($_POST)
                && sizeof($_POST) > 0);
        }

        /**
         * @static
         * @return bool
         */
        public static function isHttpGetRequestWithGetData()
        {
            return (self::isHttpGetRequest()
                && isset($_GET)
                && is_array($_GET)
                && sizeof($_GET) > 0);
        }

        /**
         * @static
         * @return bool
         */
        public static function isHttpPutRequest()
        {
            return self::isHttpRequestMethod(self::HTTP_REQUEST_TYPE_PUT);
        }

        /**
         * @static
         * @return bool
         */
        public static function isHttpDeleteRequest()
        {
            return self::isHttpRequestMethod(self::HTTP_REQUEST_TYPE_DELETE);
        }

        /**
         * @static
         * @return bool
         */
        public static function isHttpTraceRequest()
        {
            return self::isHttpRequestMethod(self::HTTP_REQUEST_TYPE_TRACE);
        }

        /**
         * @static
         * @return bool
         */
        public static function isHttpOptionsRequest()
        {
            return self::isHttpRequestMethod(self::HTTP_REQUEST_TYPE_OPTIONS);
        }

        /**
         * @static
         * @return bool
         */
        public static function isHttpConnectRequest()
        {
            return self::isHttpRequestMethod(self::HTTP_REQUEST_TYPE_CONNECT);
        }

        /**
         * @static
         * @return bool
         */
        public static function isHttpPatchRequest()
        {
            return self::isHttpRequestMethod(self::HTTP_REQUEST_TYPE_PATCH);
        }

        /**
         * @static
         * @return bool
         */
        public static function isHttpHeadRequest()
        {
            return self::isHttpRequestMethod(self::HTTP_REQUEST_TYPE_HEAD);
        }

        /**
         * @static
         * @param string $requestMethod
         * @return bool
         */
        public static function isHttpRequestMethod($requestMethod)
        {
            if (!empty($requestMethod)) {
                return ($requestMethod == self::$_httpRequestMethod);
            }
            return false;
        }

        /**
         * @return string|bool
         */
        public static function getHttpRequestMethod()
        {
            return (!empty(self::$_httpRequestMethod) ?
                self::$_httpRequestMethod : false);
        }

        /**
         * This method populates $_GET array based on routes.
         *
         * @static
         * @param \Aomebo\Runtime $ref
         * @throws \Exception
         * @return \Aomebo\Dispatcher\Route|null          matching route or null
         */
        public static function buildGetValues(& $ref)
        {

            if ($ref->isRoutable()) {

                /** @var \Aomebo\Runtime\Routable $ref */

                if ($routes = $ref->getRoutes()) {

                    foreach ($routes as $route)
                    {

                        /** @var \Aomebo\Dispatcher\Route $route */
                        if ($route->isMatching()) {

                            $route->buildGetValues();

                            if (!empty($route->method)) {
                                return $route;
                            }

                            break;

                        }

                    }
                }
            }

            return null;

        }

        /**
         * This method build the default uri for the site.
         *
         * @static
         * @return string
         */
        public static function buildDefaultUri()
        {
            if (self::isRewriteEnabled()) {
                return self::$_pageBaseUri;
            } else {
                return self::$_pageBaseUri . 'index.php';
            }
        }

        /**
         * This method build the full default uri for the site.
         *
         * @static
         * @return string
         */
        public static function buildDefaultFullUri()
        {
            return
                self::getServerUri() . self::buildDefaultUri();
        }

        /**
         * @static
         * @param string $path
         * @param bool [$toLowerCase = true]
         * @param string [$replaceWith = '-']
         * @param string|null [$charset = null]
         * @return string
         */
        public static function formatUriComponent(
            $path, $toLowerCase = true, $replaceWith = '-', $charset = null)
        {

            if (!isset($charset)) {
                $charset = \Aomebo\Configuration::
                    getSetting('output,character set');
            }

            $replace = array(
                'ö' => 'o',
                'Ö' => 'O',
                'ä' => 'a',
                'Ä' => 'Ä',
                'å' => 'a',
                'Å' => 'Å',
                '&' => '',
                '-' => ' ',
                '_' => ' ',
                ',' => ' ',
            );

            $convert =
                str_replace(
                    array_keys($replace),
                    $replace,
                    $path);

            $convert =
                preg_replace('/(\s)+/', $replaceWith, $convert);
            $convert =
                preg_replace('/[^\w\\' . $replaceWith . ']/', '', $convert);
            $convert =
                preg_replace('/(\\' . $replaceWith . ')+/', $replaceWith, $convert);

            if ($toLowerCase) {
                $convert = mb_strtolower($convert, $charset);
            }

            return $convert;

        }

        /**
         * This method build an uri for associatives.
         *
         * @static
         * @param array $getArray
         * @throws \Exception
         * @return string|bool
         */
        public static function buildAssocUri($getArray)
        {
            $uri = self::$_baseUri;
            $associativesMode =
                \Aomebo\Configuration::getSetting('settings,associatives mode');
            if (isset($getArray['at'])) {
                if ($getArray['at'] === 'css') {
                    if (self::$_rewriteEnabled) {
                        if (isset($getArray['fs'])) {
                            $uri .= $associativesMode . '.css?';
                        } else if (isset($getArray['ds'])) {
                            $uri .= $associativesMode . '.css?';
                        } else if (isset($getArray['bs'])) {
                            $uri .= 'bridge.css?';
                        }
                    } else {
                        $uri .= 'index.php?mode=' . $associativesMode . '&amp;at=css&amp;';
                    }
                } else if ($getArray['at'] === 'js') {
                    if (self::$_rewriteEnabled) {
                        if (isset($getArray['fs'])) {
                            $uri .= $associativesMode . '.js?';
                        } else if (isset($getArray['ds'])) {
                            $uri .= $associativesMode . '.js?';
                        } else if (isset($getArray['bs'])) {
                            $uri .= $associativesMode . '.js?';
                        }
                    } else {
                        $uri .= 'index.php?mode=' . $associativesMode . '&amp;at=js&amp;';
                    }
                } else {
                    Throw new \Exception('Invalid associatives type for'
                        . ' array "' . print_r($getArray, true) . '".');
                }
            } else {
                Throw new \Exception('Invalid associatives type for'
                    . ' array "' . print_r($getArray, true) . '".');
            }
            if ((isset($getArray['ds'])
                && !empty($getArray['ds']))
                || (isset($getArray['fs'])
                && !empty($getArray['fs']))
            ) {
                if (isset($getArray['ds'])) {
                    if (is_array($getArray['ds'])) {
                        $uri .= 'ds=' . implode(',', $getArray['ds']);
                    } else {
                        $uri .= 'ds=' . $getArray['ds'];
                    }
                } else if (isset($getArray['fs'])) {
                    if (is_array($getArray['fs'])) {
                        $uri .= 'fs=' . implode(',', $getArray['fs']);
                    } else {
                        $uri .= 'fs=' . $getArray['fs'];
                    }
                }
            } else {
                Throw new \Exception('No resources for '
                    . 'associative array "' . print_r($getArray, true)
                    . '".');
            }
            if (isset($getArray['cv'])
                && !empty($getArray['cv'])
            ) {
                $uri .= '&amp;cv=' . $getArray['cv'];
            }
            return $uri;
        }

        /**
         * This method builds an full assoc uri.
         *
         * @static
         * @param array $getArray
         * @return string
         */
        public static function buildAssocFullUri($getArray)
        {
            return
                self::getServerUri() . self::buildAssocUri($getArray);
        }

        /**
         * @static
         * @param string $page
         * @return bool
         */
        public static function uriExistsForPage($page)
        {
            $pagesToUri =
                \Aomebo\Configuration::getSetting('dispatch,pages uri');
            return isset($pagesToUri[$page]);
        }

        /**
         * @static
         * @param string $page
         * @throws \Exception
         * @return string
         */
        public static function getUriForPage($page)
        {
            $pagesToUri =
                \Aomebo\Configuration::getSetting('dispatch,pages uri');
            if (isset($pagesToUri[$page])) {
                return $pagesToUri[$page];
            } else {
                Throw new \Exception(
                    'Not uri exists for page "' . $page . '"');
            }
        }

        /**
         * @static
         * @param string $page
         * @return bool
         */
        public static function isDefaultPage($page)
        {
            return ($page ==
                \Aomebo\Configuration::getSetting('dispatch,default page'));
        }

        /**
         * @static
         * @return bool
         */
        public static function isCurrentPageDefaultPage()
        {
            return self::isDefaultPage(self::getPage());
        }

        /**
         * @static
         * @param string $page
         * @return bool
         */
        public static function isFileNotFoundPage($page)
        {
            return ($page ==
                \Aomebo\Configuration::getSetting('dispatch,file not found page'));
        }

        /**
         * @static
         * @return bool
         */
        public static function isCurrentPageFileNotFoundPage()
        {
            return self::isFileNotFoundPage(self::getPage());
        }

        /**
         * @static
         * @return string|bool
         */
        public static function getRequestReferer()
        {
            if (!empty($_SERVER['HTTP_REFERER'])) {
                return $_SERVER['HTTP_REFERER'];
            }
            return false;
        }

        /**
         * @static
         * @return bool
         */
        public static function requestRefererMatchesSiteUrl()
        {
            return self::requestRefererMatchesUrl(
                \Aomebo\Configuration::getSetting('site,server name'));
        }

        /**
         * @static
         * @param string $url
         * @return bool
         */
        public static function requestRefererMatchesUrl($url)
        {
            if (!empty($url)) {
                if ($requestRefer = self::getRequestReferer()) {
                    if (stripos($requestRefer, $url) !== false) {
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * This method builds and uri.
         *
         * @static
         * @param array|null [$getArray = null]
         * @param string|null [$page = null]
         * @param bool [$clear = false]
         * @param \Aomebo\Modules\Base|null [$ref = null]
         * @throws \Exception
         * @return string
         */
        public static function buildUri($getArray = null,
            $page = null, $clear = false, & $ref = null)
        {

            $uri = self::$_pageBaseUri;

            // Is rewrite enabled in server?
            if (!self::$_rewriteEnabled) {
                $uri .= 'index.php';
            }

            // Set flag to false
            $usingPage = false;

            // Is page specified?
            if (!empty($page)) {

                // Is specified page not default page?
                if (!self::isDefaultPage($page)) {

                    // Is there a uri specified for page?
                    if (self::uriExistsForPage($page)) {

                        // Add page uri
                        if (self::$_rewriteEnabled) {
                            $uri .= self::getUriForPage($page);
                        } else {
                            $uri .= '?_page=' . self::getUriForPage($page);
                        }
                        $usingPage = true;

                    } else {
                        Throw new \Exception('Invalid page "' . $page . '" specified');
                    }

                }

            // Otherwise - no page specified
            } else {

                // Is current page not file-not-found and not the default-page?
                if (self::getPage() != \Aomebo\Configuration::getSetting(
                    'dispatch,file not found page')
                    && !self::isDefaultPage(self::getPage())
                ) {

                    // Add page uri
                    if (self::$_rewriteEnabled) {
                        $uri .= self::getUriForPage(self::getPage());
                    } else {
                        $uri .= '?_page=' . self::getUriForPage(self::getPage());
                    }
                    $usingPage = true;

                }
            }

            // Is a GET array specified?
            if (isset($getArray)
                && is_array($getArray)
                && sizeof($getArray) > 0
            ) {

                if ($usingPage) {
                    if (self::$_rewriteEnabled) {
                        $uri .= '/';
                    } else {
                        $uri .= '&';
                    }
                } else {
                    if (!self::$_rewriteEnabled) {
                        $uri .= '?';
                    }
                }

                // Is reference specified, rewrite enabled and there is a matching route?
                if (self::$_rewriteEnabled
                    && self::routeExistsByUriParameters($getArray)
                ) {
                    return self::buildRouteUri($getArray, $page, $clear);
                } else {

                    $pairCount = (int) 0;

                    // Iterate through get values and make a ordinary HTTP GET query-string
                    foreach ($getArray
                        as $key => $value)
                    {
                        if (!empty($key)
                            && !empty($value)
                        ) {
                            if ($pairCount > 0) {
                                $uri .= '&';
                            } else {
                                if (self::$_rewriteEnabled) {
                                    $uri .= '?';
                                }
                            }
                            $value =
                                rawurlencode($value);
                            $uri .= $key . '=' . $value;
                            $pairCount++;
                        }
                    }
                }
            }

            return $uri;

        }

        /**
         * @static
         * @param array $uriParameters
         * @param string|null [$page = null]
         * @param bool [$clear = false]
         * @throws \Exception
         * @return string
         */
        public static function buildRouteUri(
            $uriParameters,
            $page = null,
            $clear = false)
        {
            if (isset($uriParameters)
                && is_array($uriParameters)
                && sizeof($uriParameters) > 0
            ) {
                if ($route = self::getRouteByUriParameters(
                    $uriParameters)
                ) {
                    return $route->buildUri($uriParameters, $page, $clear);
                }
            }
            Throw new \Exception(
                'Could not find route for parameters: "' . print_r(func_get_args(), true)
                . ' in ' . __FUNCTION__ . ' in ' . __FILE__);
        }

        /**
         * @static
         * @param array $uriParameters       Associative array with keys => values
         * @return bool
         */
        public static function routeExistsByUriParameters($uriParameters)
        {
            if ($hashKey = self::generateRouteHashKeyByUrlParameters(
                $uriParameters)
            ) {
                if (self::getRouteByHashKey($hashKey)) {
                    return true;
                }
            }
            return false;
        }

        /**
         * @static
         * @param array $uriParameters       Associative array with keys => values
         * @return bool|\Aomebo\Dispatcher\Route
         */
        public static function getRouteByUriParameters($uriParameters)
        {
            if ($hashKey = self::generateRouteHashKeyByUrlParameters(
                $uriParameters)
            ) {
                return self::getRouteByHashKey($hashKey);
            }
            return false;
        }

        /**
         * @static
         * @param array $uriParameters       Associative array with keys => values
         * @return string|bool
         */
        public static function generateRouteHashKeyByUrlParameters(
            $uriParameters)
        {
            if (isset($uriParameters)
                && is_array($uriParameters)
                && sizeof($uriParameters) > 0
            ) {
                if ($hashKey = \Aomebo\Dispatcher\Route::generateRouteHashKey(
                    array_keys($uriParameters))
                ) {
                    return $hashKey;
                }

            }
            return false;
        }

        /**
         * @static
         * @param string $hashKey
         * @return \Aomebo\Dispatcher\Route|bool
         */
        public static function getRouteByHashKey($hashKey)
        {
            if (!empty($hashKey)) {
                if (isset(self::$_routes[$hashKey])) {
                    return self::$_routes[$hashKey];
                }
            }
            return false;
        }

        /**
         * @static
         * @param bool $value
         */
        public static function setRewriteEnabled($value)
        {
            self::$_rewriteEnabled = (!empty($value));
        }

        /**
         * @static
         * @param string $fullRequest
         */
        public static function setFullRequest($fullRequest)
        {
            self::$_fullRequest = $fullRequest;
        }

        /**
         * @static
         * @param string $queryString
         */
        public static function setQueryString($queryString)
        {
            self::$_queryString = $queryString;
        }

        /**
         * @static
         * @return string|bool
         */
        public static function getRequestUri()
        {
            return (isset(self::$_requestUri)
                && self::$_requestUri != '' ?
                self::$_requestUri : null);
        }

        /**
         * @static
         * @param string $uri
         */
        public static function setRequestUri($uri)
        {
            self::$_requestUri = $uri;
        }

        /**
         * Notice, this method is often called without
         * this class being initialized.
         *
         * @static
         * @param \Aomebo\Dispatcher\Route $route
         * @return bool
         */
        public static function addRoute($route)
        {
            if (isset($route)) {
                if (is_object($route)
                    && is_a($route, '\Aomebo\Dispatcher\Route')
                ) {

                    /** @var \Aomebo\Dispatcher\Route $route */
                    self::$_routes[$route->getHashKey()] = $route;
                    return true;

                }
            }
            return false;
        }

        /**
         * @static
         * @param array $routes
         * @return bool
         */
        public static function addRoutes($routes)
        {
            if (isset($routes)
                && is_array($routes)
                && sizeof($routes) > 0
            ) {
                if (isset($routes['keys'])
                    && is_array($routes['keys'])
                    && sizeof($routes['keys']) > 0
                    && !empty($routes['regexp'])
                    && !empty($routes['sprintf'])
                ) {

                    $routeObject = new \Aomebo\Dispatcher\Route(
                        (!empty($routes['name']) ? $routes['name'] : null),
                        $routes['regexp'],
                        $routes['sprintf'],
                        $routes['keys'],
                        (!empty($routes['method']) ? $routes['method'] : null));

                    if ($routeObject->isValid()) {
                        return self::addRoute($routeObject);
                    }

                } else {
                    $accBool = true;
                    foreach ($routes as $route)
                    {
                        if (isset($route['keys'])
                            && is_array($route['keys'])
                            && sizeof($route['keys']) > 0
                            && !empty($route['regexp'])
                            && !empty($route['sprintf'])
                        ) {

                            $routeObject = new \Aomebo\Dispatcher\Route(
                                (!empty($route['name']) ? $route['name'] : null),
                                $route['regexp'],
                                $route['sprintf'],
                                $route['keys'],
                                (!empty($route['method']) ? $route['method'] : null));

                            if ($routeObject->isValid()) {
                                $accBool = ($accBool
                                    && self::addRoute($routeObject));
                            } else {
                                $accBool = false;
                            }

                        }
                    }
                    return $accBool;
                }
            }
            return false;
        }

        /**
         * This method builds an full assoc uri.
         *
         * @static
         * @param array|null $getArray
         * @param string|null $page
         * @param bool|null $clear
         * @param object|null $ref
         * @return string
         */
        public static function buildFullUri($getArray = null,
            $page = null, $clear = false, & $ref = null)
        {
            return
                self::getServerUri()
                . self::buildUri($getArray, $page, $clear, $ref);
        }

        /**
         * This method checks if apache mod_rewrite
         * is enabled or not.
         *
         * @internal
         * @static
         */
        private static function _parseServer()
        {
            if (self::isShellRequest()) {
                if (\Aomebo\Configuration::getSetting('site,mod_rewrite')) {
                    self::setRewriteEnabled(true);
                } else {
                    self::setRewriteEnabled(false);
                }
            } else {
                if (getenv(self::REWRITE_FLAG)) {
                    self::setRewriteEnabled(true);
                } else {
                    self::setRewriteEnabled(false);
                }
            }
        }

        /**
         * This method parses uri to find adress
         * and query-string data.
         *
         * @internal
         * @static
         */
        private static function _parseRequest()
        {

            if (!isset(self::$_baseUri)) {
                self::setBaseUri(_PUBLIC_EXTERNAL_ROOT_);
            }

            if (!isset(self::$_pageBaseUri)) {
                self::setPageBaseUri(self::$_baseUri);
            }

            /**
             * $_SERVER['REQUEST_URI'] is not a safe variable.
             *
             * @see http://security.stackexchange.com/questions/32299/is-server-a-safe-source-of-data-in-php
             */
            if (!isset(self::$_fullRequest)) {
                if (isset($_SERVER['REQUEST_URI'])
                    && substr($_SERVER['REQUEST_URI'], 0, 1) == '/'
                ) {
                    self::setFullRequest(substr(
                        $_SERVER['REQUEST_URI'], strlen(self::$_baseUri)));
                } else if (isset($_SERVER['PHP_SELF'])
                    && substr($_SERVER['PHP_SELF'], 0, 1) == '/'
                ) {
                    self::setFullRequest(basename($_SERVER['PHP_SELF']));
                } else {
                    self::setFullRequest('index.php');
                }
            }

            if (!isset(self::$_requestUri)
                || !isset(self::$_queryString)
            ) {
                if ($strrpos = strpos(
                    self::$_fullRequest, '/')
                ) {
                    self::setRequestUri(substr(self::$_fullRequest, 0, $strrpos));
                    self::setQueryString(substr(self::$_fullRequest, ($strrpos + 1)));
                } else {
                    self::setRequestUri(self::$_fullRequest);
                    self::setQueryString('');
                }
            }

            if (!isset(self::$_httpRequestMethod)) {
                if (empty($_SERVER['REQUEST_METHOD'])) {
                    self::setHttpRequestMethod(
                        self::HTTP_REQUEST_TYPE_GET);
                } else {
                    self::setHttpRequestMethod(
                        strtoupper($_SERVER['REQUEST_METHOD']));
                }
            }

        }

        /**
         * This method parses server-protocol data.
         *
         * @internal
         * @static
         */
        private static function _parseProtocol()
        {

            if (isset($_SERVER['SERVER_PROTOCOL'])) {
                $serverProtocolData = strtolower($_SERVER['SERVER_PROTOCOL']);
                $serverProtocol =
                    substr($serverProtocolData, 0, strpos($serverProtocolData, '/'));
                $serverProtocolVersion =
                    substr($serverProtocolData, strpos($serverProtocolData, '/') + 1);
            } else {
                $serverProtocol =
                    \Aomebo\Configuration::getSetting('site,protocol');
                $serverProtocolVersion =
                    \Aomebo\Configuration::getSetting('site,protocol version');
            }

            if ($serverProtocol == 'http') {
                self::$_serverProtocol = self::SERVER_PROTOCOL_HTTP;
            } else if ($serverProtocol == 'https') {
                self::$_serverProtocol = self::SERVER_PROTOCOL_HTTPS;
            }

            self::$_serverProtocolVersion = (float) $serverProtocolVersion;

        }

        /**
         * This method returns full root with transport
         * protocol and server name.
         *
         * @static
         * @return string
         */
        public static function getServerUri()
        {
            $fullRoot = '';
            if (self::$_serverProtocol == self::SERVER_PROTOCOL_HTTP) {
                $fullRoot .= 'http://';
            } else if (self::$_serverProtocol == self::SERVER_PROTOCOL_HTTPS) {
                $fullRoot .= 'https://';
            }
            if (isset($_SERVER['SERVER_NAME'])) {
                $fullRoot .= $_SERVER['SERVER_NAME'];
            } else {
                $fullRoot .=
                    \Aomebo\Configuration::getSetting('site,server name');
            }
            return $fullRoot;
        }

        /**
         * @static
         * @return string
         */
        public static function getResourcesDirExternalPath()
        {
            $resPath =
                \Aomebo\Configuration::getSetting('paths,resources dir');
            if (!\Aomebo\Configuration::getSetting(
                'paths,resources dir is absolute')
            ) {
                if (self::pathStartsWithDash($resPath)) {
                    $resPath = self::$_baseUri . substr($resPath, 1);
                } else {
                    $resPath = self::$_baseUri . $resPath;
                }
            }
            return $resPath;
        }

        /**
         * @static
         * @return string
         */
        public static function getResourcesDirInternalPath()
        {
            $resPath =
                \Aomebo\Configuration::getSetting('paths,resources dir');
            if (\Aomebo\Configuration::getSetting(
                'paths,resources dir is absolute')
            ) {
                if (stripos($resPath, 'http://')
                    || stripos($resPath, 'https://')
                ) {
                } else {
                    if (self::pathStartsWithDash($resPath)) {
                        $resPath = _PUBLIC_ROOT_ . substr($resPath, 1);
                    } else {
                        $resPath = _PUBLIC_ROOT_ . $resPath;
                    }
                }
            } else {
                if (self::pathStartsWithDash($resPath)) {
                    $resPath = _PUBLIC_ROOT_ . substr($resPath, 1);
                } else {
                    $resPath = _PUBLIC_ROOT_ . $resPath;
                }
            }
            return $resPath;
        }

        /**
         * @static
         * @return string
         */
        public static function getUploadsDirExternalPath()
        {
            $resPath =
                \Aomebo\Configuration::getSetting('paths,uploads dir');
            if (!\Aomebo\Configuration::getSetting(
                'paths,uploads dir is absolute')
            ) {
                if (self::pathStartsWithDash($resPath)) {
                    $resPath = self::$_baseUri . substr($resPath, 1);
                } else {
                    $resPath = self::$_baseUri . $resPath;
                }
            }
            return $resPath;
        }

        /**
         * @static
         * @return string
         */
        public static function getUploadsDirInternalPath()
        {
            $resPath =
                \Aomebo\Configuration::getSetting('paths,uploads dir');
            if (\Aomebo\Configuration::getSetting(
                'paths,uploads dir is absolute')
            ) {
                if (stripos($resPath, 'http://')
                    || stripos($resPath, 'https://')
                ) {
                } else {
                    if (self::pathStartsWithDash($resPath)) {
                        $resPath = _PUBLIC_ROOT_ . substr($resPath, 1);
                    } else {
                        $resPath = _PUBLIC_ROOT_ . $resPath;
                    }
                }
            } else {
                if (self::pathStartsWithDash($resPath)) {
                    $resPath = _PUBLIC_ROOT_ . substr($resPath, 1);
                } else {
                    $resPath = _PUBLIC_ROOT_ . $resPath;
                }
            }
            return $resPath;
        }

        /**
         * @static
         * @return bool
         */
        public static function isRewriteEnabled()
        {
            return (!empty(self::$_rewriteEnabled));
        }

        /**
         * @static
         * @param string $path
         * @return bool
         */
        public static function pathStartsWithQuestionMark($path)
        {
            return (isset($path)
                && strlen($path) > 0
                && substr($path, 0, 1) == '?');
        }

        /**
         * @static
         * @param string $path
         * @return bool
         */
        public static function pathStartsWithDash($path)
        {
            return (isset($path)
                && strlen($path) > 0
                && substr($path, 0, 1) == '/');
        }

        /**
         * @static
         * @param string $path
         * @return bool
         */
        public static function pathIsPageSyntax($path)
        {
            if (!empty($path)
                && preg_match(
                    self::getPageSyntaxRegexp(),
                    $path,
                    $matches) === 0
            ) {
                return true;
            }
            return false;
        }

        /**
         * This method parses adress to find page.
         *
         * @internal
         * @static
         * @throws \Exception
         */
        private static function _parsePage()
        {

            $uriToPages =
                \Aomebo\Configuration::getSetting('dispatch,uri pages');
            $defaultPage =
                \Aomebo\Configuration::getSetting('dispatch,default page');

            self::setFileNotFoundFlag(false);

            // Is it a shell request?
            if (self::isShellRequest()) {

                // Can we find any arguments?
                if ($shellArguments = self::getShellArguments()) {

                    self::setPage($shellArguments[1]);

                // Otherwise - use default page as page
                } else {

                    self::setPage($defaultPage);
                }

            } else if (self::isFaviconRequest()) {

                $favIconPath =
                    self::getResourcesDirInternalPath()
                    . DIRECTORY_SEPARATOR
                    . \Aomebo\Configuration::getSetting('site,shortcut icon');

                if (file_exists($favIconPath)) {

                    self::setHttpHeaderField(
                        'Content-Type',
                        'image/vnd.microsoft.icon; charset=binary');
                    self::setHttpHeaderField('Content-Disposition',
                    'inline; filename="favicon.ico"');
                    readfile($favIconPath);

                } else {
                    Throw new \Exception(
                        'Could not find favicon at "' . $favIconPath . '"');
                }

            } else if (self::isPageRequest()) {

                // Is a uri specified?
                if (self::getRequestUri()) {

                    // Is rewrite disabled and uri is index.php?
                    if (!self::isRewriteEnabled()
                        && self::getRequestUri() == 'index.php'
                    ) {

                        self::setPage($defaultPage);

                    // Otherwise - is rewrite enabled and query string starts with ?
                    } else if (self::isRewriteEnabled()
                        && (self::pathStartsWithQuestionMark(self::getRequestUri())
                        || self::pathIsPageSyntax(self::getRequestUri()))
                    ) {

                        self::setPage($defaultPage);
                        self::setQueryString(self::getFullRequest());
                        self::setRequestUri('');

                    // Otherwise - parse uri
                    } else {

                        $hit = false;

                        // Iterate through uri routes..
                        foreach ($uriToPages as $uri => $page)
                        {

                            // Is rewrite enabled and uri exactly matches this route,
                            // or is rewrite disabled and GET-page parameter exactly matches route
                            if ((self::$_rewriteEnabled
                                && self::$_requestUri === $uri)
                                || (!self::$_rewriteEnabled
                                && isset($_GET['_page'])
                                && $_GET['_page'] === $uri)
                            ) {

                                self::setPage($page);
                                $hit = true;
                                break;

                            }

                        }

                        // Could we find a matching route for page?
                        if ($hit) {

                            if (self::isFileNotFoundPage(self::getPage())) {

                                self::removeCurrentUriFromIndexing();
                                self::setHttpResponseStatus404NotFound();

                            } else if (self::isDefaultPage(self::getPage())) {

                                $newUri =
                                    self::getPageBaseUri()
                                    . self::getQueryString();

                                self::setHttpResponseStatus301MovedPermanently();
                                self::setHttpHeaderFieldLocation($newUri);
                                exit;

                            }

                        // Otherwise - file not found
                        } else {

                            // Is it a HTTP request?
                            if (self::isHttpRequest()) {

                                // Flag that file could not be found and restart interpretation
                                self::fileNotFound(true);

                            }
                        }
                    }

                // Otherwise - use default page
                } else {
                    self::setPage($defaultPage);
                }
            }

        }

    }
}
