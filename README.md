qencode-api-php-client
====================
PHP library for interacting with the Qencode API.

### Installation

   Get composer, run this in your project directory:
```bash
    curl -sS https://getcomposer.org/installer | php
```

   Create composer.json under the root of your project with the following instructions:
```json
    {
      "require": {
        "qencode/api-client": "1.02.*"
      }
    }
``` 
Run composer:
```bash
    php composer.phar install
```

Include vendor/autoload.php:
```php
   require 'vendor/autoload.php';
```

If you don't use composer, use autoload.php located in the root of the repo:
```php
   require_once __DIR__ . '/../autoload.php';
```

### Usage

Instantiate Qencode API Client:
```php
   $q = new QencodeApiClient($apiKey);
```
Create a JSON query: 

```php
$params = '
{"query": {
  "source": "https://nyc3.s3.qencode.com/qencode/bbb_30s.mp4",
  "format": [
    {
      "output": "mp4",
      "size": "320x240",
      "video_codec": "libx264"
    }
  ]
  }
}';
```

Create a new job:

```php
   $task = $q->createTask();

   $task->startCustom($params);
```

Query an existing job:

```php
    $response = $task->getStatus();
```

## Copyright
Copyright 2021 Qencode, Inc.