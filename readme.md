API Manager
===================
>API Manager Package for Laravel 4


### Install
Via composer:

```php
{
     "require": {
         "vinelab/cdn": "*"
     }
 }
```
 
Add the service provider to `app/config/app.php`:

```php
'providers' => array(
    ...
    'Vinelab\Api\ApiServiceProvider',

),
```

## Configuration

Publish the package config file:
```dos
php artisan config:publish vinelab/api
```
and check it out at `app/config/packages/vinelab/api/api.php`

You need to enter the mappers base namespace

```php
'mappers' => 'Lib\Api\Mappers\\',
```


## Usage

1. you need to create a mapper for each model you want to return, and each mapper must use the `Mappable` trait, in order to implement the `map()` function.
here's an example of a mapper called `PostsMapper.php`

```php
<?php namespace Lib\Api\Mappers;

use Vinelab\Api\Mappable;

class PostsMapper implements PostsInterface {

    use Mappable;

    public function map($data)
    {
        return [
            'id'     => (int) $data['id'],
            'title'  => $data['title'],
            'text'   => $data['text'],
            'active' => (boolean) $data['active']
        ];
    }

}

```

2. From your controller you can use the `Api` Facade Class that contains these 2 important functions `respond` and `error`

### Examples
 example 1: with Laravel pagination
```php
$data = Post::paginate(5);

return Api::respond('PostsMapper', $data);
// or: Api::respond('PostsMapper', $data, 200);
```
example 2: without pagination
```php
$data = Post::All();

return Api::respond('PostsMapper', $data);
```
example 3: with custom pagination
```php
$data = Post::where('active', '0')->get();
$total = $data->count();
$page = 2; // Input::get('page') ...

return Api::respond('PostsMapper', $data, $total, $page);
```

Error Example:
```php
} catch (WhateverCustomException $e)
{
	return Api::error($e);
}
```

RESULT EXAMPLE:
```json
{
    "status": 200,
    "total": 30,
    "page": 1,
    "data": [
        {
            "id": 1,
            "title": "Ex veniam et voluptatibus est. Enim provident tempore reiciendis qui qui. Aut soluta ipsum voluptatem repellat quod explicabo.",
            "text": "Voluptatem dolorum eum sequi maiores quo facere dolor. Molestiae corrupti rem quo sed. Quibusdam ut voluptate consequatur.",
            "active": false
        },
        {
            "id": 2,
            "title": "Qui aperiam aut voluptatem repellat est. Minima dolor qui rem sint cum debitis. Ab quia neque quasi laboriosam.",
            "text": "Ea et quae facere fugiat non est eveniet. Veniam quas doloremque repellat esse nihil qui qui voluptas. Laboriosam voluptate rerum et perferendis adipisci deleniti. Quae quam nisi facilis quia dolore.",
            "active": false
        },
        {
            "id": 3,
            "title": "Aspernatur voluptas id ratione rerum et quis. Repellendus dolorem nihil sint maxime. Dolorum ex dolorum sit est recusandae.",
            "text": "Sit voluptatem voluptatem corporis. Excepturi eligendi quia maiores nesciunt quia. Ipsum voluptatem autem aspernatur pariatur.",
            "active": false
        }
    ]
}
```

*(Optionally)* you can extend the `ApiException`:
```php
use Vinelab\Api\ApiException;

class WhateverCustomException extends ApiException{
	...
}
```


