API Manager
===================
>API Manager Package for Laravel 4


### Install
Via composer:

```php
{
     "require": {
         "vinelab/api": "*"
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

You need to set the mappers base namespace

```php
'mappers' => 'Lib\Api\Mappers\\',
```


## Setup

Create a mapper for each model you want to return, and each mapper must use the `MappableTrait` trait, in order to implement the `map()` function.
here's an example of a mapper called `PostsMapper.php`.
Note: you can override the argument data type of the `map` function, it can be an `array` or an object of the `model`

Example mapper for a Post model:
```php
<?php namespace Lib\Api\Mappers;

use Vinelab\Api\MappableTrait;

class PostsMapper implements PostsInterface {

    use MappableTrait;

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

### Usage and Responses
From your controller you can use the `Api` Facade Class that contains these 2 important functions `respond` and `error`.

The `Api::respond` can take different types of parameters, it can be instance of `Illuminate\Pagination\Paginator` or a  `model` object or a `Illuminate\Database\Eloquent\Collection` of model objects or an `array`.

The responses returned by this package follows the conventions of a [json api](http://jsonapi.org/format/) and all the standards of the [Build APIs You Won't Hate](https://leanpub.com/build-apis-you-wont-hate) book.

 Pagination:
```php
$data = Post::paginate(3);

return Api::respond('PostsMapper', $data);
// or: Api::respond('PostsMapper', $data, 200);
```
Response sample:

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

Model:
```php
$data = Post::first();

return Api::respond('PostsMapper', $data);
```

Response sample:
```json
{
    "status": 200,
    "data": {
        "id": 1,
        "title": "Ex veniam et voluptatibus est. Enim provident tempore reiciendis qui qui. Aut soluta ipsum voluptatem repellat quod explicabo.",
        "text": "Voluptatem dolorum eum sequi maiores quo facere dolor. Molestiae corrupti rem quo sed. Quibusdam ut voluptate consequatur.",
        "active": false
    }
}
```

Collection:
```php
$data = Post::where('active', '0')->get();
$total = $data->count();
$page = 2; // Input::get('page') ...

return Api::respond('PostsMapper', $data, $total, $page);
```

Response sample:
```json
{
    "status": 200,
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
			............
        {
            "id": 27,
            "title": "Illo quia minima ut est praesentium assumenda explicabo. Facilis ipsam minus et rerum perspiciatis illo. Voluptas distinctio et possimus non iste doloremque dolor.",
            "text": "Corporis quos dignissimos voluptas tempora quo perspiciatis nesciunt. Corrupti soluta ad eos tenetur debitis. Aut quia atque molestiae delectus et.",
            "active": false
        },
        {
            "id": 28,
            "title": "Labore sequi molestiae quisquam nostrum. Esse nisi in non aut praesentium occaecati. Suscipit exercitationem necessitatibus eos quis nulla. Necessitatibus nisi nostrum non ducimus aspernatur quod.",
            "text": "Officiis odio cumque est expedita. Qui atque veniam eos saepe. Architecto corrupti quis quia modi voluptatem.",
            "active": false
        }
    ]
}
```


####Error Handling:
To response with an error use the `Api::error` and pass to it an `Exception` class or your custom class that extends from `Exception` otherwise you can pass a `string` error message 
```php
} catch (WhateverCustomException $e)
{
	return Api::error($e);
}
```

Response sample:
```json
{
    "status": 500,
    "error": {
        "message": "Something is wrong!!!",
        "code": 0
    }
}
```
