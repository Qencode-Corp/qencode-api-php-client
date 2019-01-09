# qencode-api-php-client
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
        "qencode/api-client": "1.0.*"
      },
      "autoload": {
        "classmap": [
          "vendor/qencode/api-client/src/"
        ]
      }
    }
``` 
   Run composer:
```bash
    php composer.phar install
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
    

Create a new job:

```php
   $task = $q->createTask(); 
   $task->start($transcodingProfileId, $video_url);
```

Query an existing job:

```php
    $response = $task->getStatus();
```

## Copyright
Copyright 2018 Qencode, Inc.