<?php

namespace Vektor\Api;

use Illuminate\Http\Request;

class Api
{
    public function __construct()
    {
        return $this;
    }

    public static function encryptData($data)
    {
        $iv = random_bytes(16); // Ensure a 16-byte IV
        $key = hash('sha256', config('api.encryption_key'), true); // Convert key to 32-byte format
        $cipher = 'AES-256-CBC';

        // Encrypt the JSON-encoded data
        $encrypted = openssl_encrypt(json_encode($data), $cipher, $key, OPENSSL_RAW_DATA, $iv);

        // Ensure Base64 encoding of both IV + Encrypted Data
        return base64_encode($iv.$encrypted);
    }

    public function validateRequest(Request $request, $salt)
    {
        if (
            empty($request->server('HTTP_CSRFP_TOKEN'))
            || empty($request->server('HTTP_CSRFP_REQUEST'))
            || !$this->verifyToken($request->server('HTTP_CSRFP_TOKEN'), $request->server('HTTP_CSRFP_REQUEST'), $salt)
        ) {
            throw new \Exception('Oooops! Your token is malformed.');
        }

        return true;
    }

    public function generateToken(Request $request)
    {
        if (
            !empty($request->server('HTTP_REQUEST_ACTION'))
            && !empty($request->input('request_action'))
            && $request->server('HTTP_REQUEST_ACTION') === $request->input('request_action')
        ) {
            $data_str = $request->input('request_action');
            $timeout = config('api.timeout');
            $salt = config('api.salt');

            $hash_time = microtime(true);
            $range = mt_rand(4, 25);
            $random = bin2hex(openssl_random_pseudo_bytes($range));
            $hash = hash_hmac('sha256', "{$data_str}-{$hash_time}-{$timeout}-{$random}", $salt);

            $response_data = "{$hash}-{$hash_time}-{$timeout}-{$random}";
        } else {
            $response_data = null;
        }

        return $response_data;
    }

    public function verifyToken($token, $data_str, $salt)
    {
        $token_pieces = explode('-', $token, 4);

        if (4 !== count($token_pieces)) {
            return false;
        }

        [$hash, $hash_time, $timeout, $random] = $token_pieces;

        if (
            empty($hash)
            || empty($hash_time)
            || empty($timeout)
            || empty($random)
        ) {
            return false;
        }

        if (microtime(true) > $hash_time + $timeout) {
            return false;
        }

        $check_string = "{$data_str}-{$hash_time}-{$timeout}-{$random}";
        $check_hash = hash_hmac('sha256', $check_string, $salt);

        if ($check_hash === $hash) {
            return true;
        }

        return false;
    }

    public static function getHttpCodeDescription($code)
    {
        $codes = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing', // WebDAV; RFC 2518
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information', // since HTTP/1.1
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status', // WebDAV; RFC 4918
            208 => 'Already Reported', // WebDAV; RFC 5842
            226 => 'IM Used', // RFC 3229
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other', // since HTTP/1.1
            304 => 'Not Modified',
            305 => 'Use Proxy', // since HTTP/1.1
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect', // since HTTP/1.1
            308 => 'Permanent Redirect', // approved as experimental RFC
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot', // RFC 2324
            419 => 'Authentication Timeout', // not in RFC 2616
            420 => 'Enhance Your Calm', // Twitter
            420 => 'Method Failure', // Spring Framework
            422 => 'Unprocessable Entity', // WebDAV; RFC 4918
            423 => 'Locked', // WebDAV; RFC 4918
            424 => 'Failed Dependency', // WebDAV; RFC 4918
            424 => 'Method Failure', // WebDAV)
            425 => 'Unordered Collection', // Internet draft
            426 => 'Upgrade Required', // RFC 2817
            428 => 'Precondition Required', // RFC 6585
            429 => 'Too Many Requests', // RFC 6585
            431 => 'Request Header Fields Too Large', // RFC 6585
            444 => 'No Response', // Nginx
            449 => 'Retry With', // Microsoft
            450 => 'Blocked by Windows Parental Controls', // Microsoft
            451 => 'Redirect', // Microsoft
            451 => 'Unavailable For Legal Reasons', // Internet draft
            494 => 'Request Header Too Large', // Nginx
            495 => 'Cert Error', // Nginx
            496 => 'No Cert', // Nginx
            497 => 'HTTP to HTTPS', // Nginx
            499 => 'Client Closed Request', // Nginx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates', // RFC 2295
            507 => 'Insufficient Storage', // WebDAV; RFC 4918
            508 => 'Loop Detected', // WebDAV; RFC 5842
            509 => 'Bandwidth Limit Exceeded', // Apache bw/limited extension
            510 => 'Not Extended', // RFC 2774
            511 => 'Network Authentication Required', // RFC 6585
            598 => 'Network read timeout error', // Unknown
            599 => 'Network connect timeout error', // Unknown
        ];
        if (isset($codes[$code])) {
            return $codes[$code];
        }

        return "Unknown http status code: {$code}";
    }

    public static function transformResponse($data = null)
    {
        if (null === $data) {
            $data = new \stdClass();
        }

        if ('array' == gettype($data)) {
            $data = (object) $data;
        }

        $response = [
            'success' => (isset($data->success) && !empty($data->success)) ? $data->success : false,
            'success_message' => (isset($data->success_message) && !empty($data->success_message)) ? $data->success_message : null,
            'error' => (isset($data->error) && !empty($data->error)) ? $data->error : false,
            'error_message' => (isset($data->error_message) && !empty($data->error_message)) ? $data->error_message : null,
            'http_code' => (isset($data->http_code) && !empty($data->http_code)) ? $data->http_code : 200,
            'http_message' => null,
            'data' => (isset($data->data) && !empty($data->data)) ? $data->data : null,
        ];

        if (isset($data->edata) && !empty($data->edata)) {
            $response['data'] = self::encryptData($data->edata);
        }

        $response['http_message'] = (isset($data->http_message) && !empty($data->http_message)) ? $data->http_message : self::getHttpCodeDescription($response['http_code']);

        return $response;
    }

    public function response($data = null)
    {
        $transformed_data = self::transformResponse($data);

        return response($transformed_data, $transformed_data['http_code']);
    }
}
