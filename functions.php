<?php
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

function is_exists_web_object($url) {
    if ($url === '') {
        return false;
    }
    $response = get_web_object($url, true);

    if ($response['code'] === 200) {
        $status = true;
    } else {
        $status = false;
    }

    return $status;
}

function response_error_json($code, $message) {
    return json_encode((object)array(
        'error' => array(
            'code'    => $code,
            'message' => $message
        )
    ), JSON_UNESCAPED_UNICODE);
}

function simple_log($level, $message) {
    echo '[' . $level . ']' . "\t" . date('Y-m-d H:i:s') . "\t" . $message . "\n";
}

function log_info($message) {
    simple_log('INFO', $message);
}

function log_warn($message) {
    simple_log('WARN', $message);
}

function get_rss($url) {
    $response = get_web_object($url);
    if ($response['code'] !== 200) {
        throw new Exception('RSS url is not valid');
    }
    try {
        $feed = simplexml_load_string($response['body'], 'SimpleXMLElement', LIBXML_NOCDATA);
    } catch (Exception $e) {
        throw new Exception('RSS can not parsing');
    }
    return $feed;
}

