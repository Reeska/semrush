Semrush API PHP client
========

Library to easily querying Semrush API. All web services are not implemented yet.

## Implemented Features
* Domain Overview (All databases)
* Domain Organic Search Keywords

## Usage

Example : you want to get all keywords and their positions for the website github.com :

```php
$semrush = new SemrushAPI('api_key');
$results = $semrush->organicSearchKeywords('github.com');

foreach($results as $result) {
  echo 'Keyword : '. $result->keyword();
  echo 'Position : '. $result->position();
}
```

See Semrush documentation here : http://www.semrush.com/api-documentation/
