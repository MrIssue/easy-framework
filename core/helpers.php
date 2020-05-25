<?php
if (! function_exists('response')) {
    function response($content = '', $status = 200, array $headers = [])
    {
        return new Core\Http\Response($content, $status, $headers);
    }
}
if (!function_exists('request')) {
    /**
     * @return Core\Http\Request
     */
    function request()
    {
        return Core\Http\Request::getInstance();
    }
}

if (! function_exists('config')) {
    function config($key, $default = null)
    {
        if (is_array($key)) {
            Core\Config::getInstance()->set($key);
        }

        return Core\Config::getInstance()->get($key, $default);
    }
}

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        if (strlen($value) > 1 && starts_with($value, '"') && ends_with($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
/**
 * Determine if a given string starts with a given substring.
 *
 * @param  string  $haystack
 * @param  string|array  $needles
 * @return bool
 */
if (! function_exists('starts_with')) {
    function starts_with($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }

}

/**
 * Determine if a given string ends with a given substring.
 *
 * @param  string  $haystack
 * @param  string|array  $needles
 * @return bool
 */
if (! function_exists('ends_with')) {
    function ends_With($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string)$needle) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (! function_exists('abort')) {
    function abort($status = 404, $content = '')
    {
        http_response_code($status);

        echo $content;

        die(1);
    }
}

if (! function_exists('redirect')) {
    function redirect($url, $status = 302)
    {
        header('Location: ' . $url);
        http_response_code($status);

        die(1);
    }
}

if (! function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed  $args
     * @return void
     */
    function dd(...$args)
    {
        http_response_code(500);
        foreach ($args as $x) {
            var_dump($x);
        }

        die(1);
    }
}

if (! function_exists('storage_path')) {
    /**
     * Get the path to the storage folder.
     *
     * @param  string  $path
     * @return string
     */
    function storage_path($path = '')
    {
        return STORAGE_PATH.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (! function_exists('public_path')) {
    function public_path($path = '')
    {
        return ROOT_PATH.DIRECTORY_SEPARATOR . 'public' .($path ? DIRECTORY_SEPARATOR.ltrim($path, DIRECTORY_SEPARATOR) : $path);
    }
}

if (! function_exists('file_cache')) {
    function file_cache()
    {
        return Core\Cache\FileCache::getInstance()->setDefaultDir();
    }
}

if (! function_exists('str_after')) {
    /**
     * Return the remainder of a string after a given value.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    function str_after($subject, $search)
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }
}

if (! function_exists('array_get')) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    function array_get($array, $key, $default = null)
    {
        return Core\Plugins\Arr::get($array, $key, $default);
    }
}