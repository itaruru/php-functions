<?php
namespace functions\http;

/**
 * @param $url
 * @param bool $is_nobody
 *
 * @return array|bool
 */
function get_web_object($url, $is_nobody = false) {
    if ($url === '') {
        return false;
    }
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, $is_nobody);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return array(
        'code' => (int)$code,
        'body' => (string)$body
    );
}

/**
 * @param $url
 *
 * @return bool
 */
function is_exists_web_object($url) {
    if ($url === '') {
        return false;
    }
    $response = get_web_object($url, true);
    return $response['code'] === 200;
}

/**
 * @param $code
 * @param $message
 *
 * @return string
 */
function response_error_json($code, $message) {
    return json_encode((object)array(
        'error' => array(
            'code'    => $code,
            'message' => $message
        )
    ), JSON_UNESCAPED_UNICODE);
}

/**
 * @param string $url
 *
 * @return \SimpleXMLElement
 * @throws \Exception
 */
function get_rss($url) {
    $response = get_web_object($url);

    if ($response['code'] !== 200) {
        throw new \Exception('RSS url is not valid');
    }

    try {
        @$feed = simplexml_load_string($response['body'], 'SimpleXMLElement', LIBXML_NOCDATA);
    } catch (\Exception $e) {
        throw new \Exception('RSS can not parsing');
    }

    return $feed;
}

namespace functions\log;

/**
 * @param $level
 * @param $message
 */
function simple_log($level, $message) {
    echo '[' . $level . ']' . "\t" . date('Y-m-d H:i:s') . "\t" . $message . "\n";
}

/**
 * @param $message
 */
function log_info($message) {
    simple_log('INFO', $message);
}

/**
 * @param $message
 */
function log_warn($message) {
    simple_log('WARN', $message);
}

namespace functions\html;

/**
 * converting your html tag string.<br>
 * adding the double quotations in your html tag.
 *
 * <code>
 * require_once __DIR__ . '/functions.php';
 *
 * use functions\html;
 *
 * $html = '<font color=red>red string</font>';
 * echo html\add_html_attr_quotes($html);
 *   //=> <font color="red">red string</font>
 *
 * $html = '<font color=red size=2>red string</font>';
 * echo html\add_html_attr_quotes($html);
 *   //=> <font color="red" size="2">red string</font>
 * </code>
 *
 * @param  string $html
 * @return string html string
 */
function add_html_attr_quotes($html) {
    $pattern = '/(\\w+)\s*=\\s*("[^"]*"|\'[^\']*\'|[^"\'\\s>]*)/';
    return preg_replace_callback($pattern, function($matches) {
        return add_html_attr_quotes_callback($matches);
    }, $html, -1);
}

/**
 * @param  array   $matches
 * @return string
 */
function add_html_attr_quotes_callback($matches) {
    return $matches[1] . '="' . str_replace(array('"', "'"), '', $matches[2]) . '"';
}

/**
 * @param string $buffer
 * @see https://stackoverflow.com/questions/6225351/how-to-minify-php-page-html-output
 * @return mixed
 */
function sanitize_output($buffer) {
    $search = array(
        '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
        '/[^\S ]+\</s',     // strip whitespaces before tags, except space
        '/(\s)+/s',         // shorten multiple whitespace sequences
        '/<!--(.|\s)*?-->/' // Remove HTML comments
    );
    $replace = array(
        '>',
        '<',
        '\\1',
        ''
    );
    $buffer = preg_replace($search, $replace, $buffer);
    return $buffer;
}
