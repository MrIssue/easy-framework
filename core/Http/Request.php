<?php
namespace Core\Http;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = static::createFromGlobals();
        }

        return self::$instance;
    }

    public function instance()
    {
        return $this;
    }

    public function validateEtag($key = null, bool $weak = false)
    {
        if ($key == null) {
            return;
        }

        $etag = hash_hmac('md5', $key, config('app.key'));
        if (in_array(true === $weak ? 'W\/' : '"' . $etag .'"', $this->getETags())) {
            return response()->setStatusCode(304);
        }
    }

    /**
     * Get the request method.
     *
     * @return string
     */
    public function method()
    {
        return $this->getMethod();
    }

    /**
     * Get the root URL for the application.
     *
     * @return string
     */
    public function root()
    {
        return rtrim($this->getSchemeAndHttpHost().$this->getBaseUrl(), '/');
    }

    /**
     * Get the URL (no query string) for the request.
     *
     * @return string
     */
    public function url()
    {
        return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
    }

    /**
     * Get the full URL for the request.
     *
     * @return string
     */
    public function fullUrl()
    {
        $query = $this->getQueryString();

        $question = $this->getBaseUrl().$this->getPathInfo() == '/' ? '/?' : '?';

        return $query ? $this->url().$question.$query : $this->url();
    }

    /**
     * Get the full URL for the request with the added query string parameters.
     *
     * @param  array  $query
     * @return string
     */
    public function fullUrlWithQuery(array $query)
    {
        $question = $this->getBaseUrl().$this->getPathInfo() == '/' ? '/?' : '?';

        return count($this->query()) > 0
            ? $this->url().$question.http_build_query(array_merge($this->query(), $query))
            : $this->fullUrl().$question.http_build_query($query);
    }

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function path()
    {
        $pattern = trim($this->getPathInfo(), '/');

        return $pattern == '' ? '/' : $pattern;
    }

    /**
     * Get the current decoded path info for the request.
     *
     * @return string
     */
    public function decodedPath()
    {
        return rawurldecode($this->path());
    }

    public function ajax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Determine if the request is the result of an PJAX call.
     *
     * @return bool
     */
    public function pjax()
    {
        return $this->headers->get('X-PJAX') == true;
    }

    /**
     * Determine if the request is over HTTPS.
     *
     * @return bool
     */
    public function secure()
    {
        return $this->isSecure();
    }

    /**
     * Get the client IP address.
     *
     * @return string
     */
    public function ip()
    {
        return $this->getClientIp();
    }

    /**
     * Get the client IP addresses.
     *
     * @return array
     */
    public function ips()
    {
        return $this->getClientIps();
    }

    /**
     * Get the client user agent.
     *
     * @return string
     */
    public function userAgent()
    {
        return $this->headers->get('User-Agent');
    }

    /**
     * Retrieve a server variable from the request.
     *
     * @param  string  $key
     * @param  string|array|null  $default
     * @return string|array
     */
    public function server($key = null, $default = null)
    {
        return $this->server->get($key, $default);
    }

    /**
     * Determine if a header is set on the request.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasHeader($key)
    {
        return ! is_null($this->header($key));
    }

    public function headers()
    {
        $headers = [];
        foreach ($this->headers->all() as $key => $header) {
            if (null === $header[0]) {
                $headers[$key] = null;
            }

            $headers[$key] = (string) $header[0];
        }

        return $headers;
    }

    /**
     * @param null $key
     * @param null $default
     * @return array|null|string|string[]
     */
    public function header($key = null, $default = null)
    {
        if ($key) {
            return $this->headers->get($key, $default);
        } else {
            return $this->headers->all();
        }
    }

    /**
     * @param null $key
     * @param null $default
     * @return array|mixed
     */
    public function query($key = null, $default = null)
    {
        if ($key) {
            return $this->query->get($key, $default);
        } else {
            return $this->query->all();
        }
    }

    /**
     * Retrieve a request payload item from the request.
     *
     * @param  string  $key
     * @param  string|array|null  $default
     *
     * @return string|array
     */
    public function post($key = null, $default = null)
    {
        return $this->request->get($key, $default);
    }
}
