<?php

require_once __DIR__ . '/../vendor/autoload.php';

const BROWSER_PROTO_MIRROR_URL = 'https://raw.githubusercontent.com/ChromeDevTools/devtools-protocol/master/json/browser_protocol.json';
const JS_PROTO_MIRROR_URL = 'https://raw.githubusercontent.com/ChromeDevTools/devtools-protocol/master/json/js_protocol.json';

$client = new GuzzleHttp\Client();
$response = $client->request(
    'GET',
    BROWSER_PROTO_MIRROR_URL
);
$browserProtocol = $response->getBody()->getContents();

$response = $client->request(
    'GET',
    JS_PROTO_MIRROR_URL
);
$jsProtocol = $response->getBody()->getContents();


if (!file_put_contents(__DIR__ . '/../browser_protocol.json', $browserProtocol)) {
    echo 'Failed to write a browser protocol file.\n';
    exit(1);
}

if (!file_put_contents(__DIR__ . '/../js_protocol.json', $jsProtocol)) {
    echo 'Failed to write a js protocol file.\n';
    exit(1);
}

// Fixme: add cache timestamp.
