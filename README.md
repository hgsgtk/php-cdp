# php-cdp

[![MIT License](https://img.shields.io/github/license/hgsgtk/php-cdp)](https://github.com/hgsgtk/php-cdp/blob/main/LICENSE)

## Description

Chrome DevTools Protocol binding for PHP

## Installation

Install via composer.

```bash
composer require hgsgtk/php-cdp
```

## Setup

### Chrome/Chromium

Start Chrome with the `--remote-debugging-port` option, for example:

```bash
$ /Applications/Google\ Chrome.app/Contents/MacOS/Google\ Chrome \
 --remote-debugging-port=9222 \
 --no-first-run \
 --no-default-browser-check \
 --user-data-dir=$(mktemp -d -t 'chrome-remote_data_dir')

 DevTools listening on ws://127.0.0.1:9222/devtools/browser/3d57cf62-84ae-4f71-bfa4-3c38b58dcece
```
