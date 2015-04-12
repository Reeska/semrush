Semrush API PHP client
========

Library to easily querying Semrush API. All web services are not implemented yet.

## Implemented Features
* Domain Overview (All databases)
* Domain Organic Search Keywords
* Domain Overview (History)

## Download

You can use composer to retrieve this library like below (recommanded method) :

```
$ php composer.phar require "reeska/semrush" "dev-master"
```

Packagist URL : https://packagist.org/packages/reeska/semrush

## Usage

Example : you want to get all keywords and their positions for the website github.com :

```php
require_once __DIR__.'/vendor/autoload.php';

use Reeska\Semrush\SemrushAPI;

$semrush = new SemrushAPI('api_key');
$results = $semrush->organicSearchKeywords('github.com');

foreach($results as $result) {
  echo 'Keyword : '. $result->keyword();
  echo 'Position : '. $result->position();
}
```

See Semrush documentation here : http://www.semrush.com/api-documentation/
