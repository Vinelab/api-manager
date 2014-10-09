API Manager
======
>API Manager Package for Laravel 4


## Install

######Via composer:
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
php artisan config:publish vinelab/api-manager
```
Open it the config file at:
`app/config/packages/vinelab/api/api.php`

You need to set the mappers base namespace

```php
'mappers' => 'Lib\Api\Mappers\\',
```


## Setup
This package expects to have **Mapper class** for each model you want to return via the API.
The class name should follow this convention `modelnameMapper` example (PostsMapper).
Each mapper class must  have an implementation of a `map()` function, thus you should use the API `MappableTrait` trait. 
>*(traits in php acts similarly to interfaces, with the ability to override the function signature)*.


here's an example of a mapper called `PostsMapper.php`

Note: you can override the argument data type of the `map()` function, to accept an `array` or an object of the `model`, or anything else you prefer.

###Mapper example
####Array input 
```php
<?php namespace Lib\Api\Mappers;

use Vinelab\Api\MappableTrait;

class PostsMapper implements PostsInterface {

    use MappableTrait;

    public function map(array $data)
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
#####It's interface (PostsInterface)

```php
<?php namespace Lib\Api\Mappers;

interface PostsInterface
{
    public function map(array $data);
}
```

####Model input 
```php
<?php namespace Lib\Api\Mappers;

use Vinelab\Api\MappableTrait;

class PostsMapper implements PostsInterface {

    use MappableTrait;

    public function map(Post $post)
    {
        return [
            'id'     => (int) $post->id,
            'title'  => $post->title,
            'text'   => $post->text,
            'active' => (boolean) $post->active
        ];
    }

}
```

#####It's interface (PostsInterface)

```php
<?php namespace Lib\Api\Mappers;

interface PostsInterface
{
    public function map(Post $data);
}
```


## Usage and Responses
From your controller you can use the `Api` Facade Class that contains these 2 important functions `respond` and `error`.

The `Api::respond` takes different types of parameters, it can be instance of `Illuminate\Pagination\Paginator` or a  `model` object or a `Illuminate\Database\Eloquent\Collection` of model objects or an `array`.

The responses returned by this package follows the conventions of a [json api](http://jsonapi.org/format/) and the standards of [Build APIs You Won't Hate](https://leanpub.com/build-apis-you-wont-hate).

### Pagination
```php
$data = Post::paginate(3);

return Api::respond('PostsMapper', $data);
// or: Api::respond('PostsMapper', $data, 200);
```
####Response sample

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

###Model
```php
$data = Post::first();

return Api::respond('PostsMapper', $data);
```

####Response sample
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

###Collection:
```php
$data = Post::where('active', '0')->get();
$total = $data->count();
$page = 2; // Input::get('page') ...

return Api::respond('PostsMapper', $data, $total, $page);
```

####Response sample:
```json
{
    "status": 200,
    "total": 15,
    "page": 2,
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
            "id": 14,
            "title": "Illo quia minima ut est praesentium assumenda explicabo. Facilis ipsam minus et rerum perspiciatis illo. Voluptas distinctio et possimus non iste doloremque dolor.",
            "text": "Corporis quos dignissimos voluptas tempora quo perspiciatis nesciunt. Corrupti soluta ad eos tenetur debitis. Aut quia atque molestiae delectus et.",
            "active": false
        },
        {
            "id": 15,
            "title": "Labore sequi molestiae quisquam nostrum. Esse nisi in non aut praesentium occaecati. Suscipit exercitationem necessitatibus eos quis nulla. Necessitatibus nisi nostrum non ducimus aspernatur quod.",
            "text": "Officiis odio cumque est expedita. Qui atque veniam eos saepe. Architecto corrupti quis quia modi voluptatem.",
            "active": false
        }
    ]
}
```


##Error Handling:
For error response use the `Api::error` function.
`Api::error` takes an `Exception` class or a `custom class` that extends from `Exception` or a `string` of the error message. 

###Exception example
```php
} catch (WhateverCustomException $e)
{
	return Api::error($e);
}
```

####Response sample:
```json
{
    "status": 500,
    "error": {
        "message": "Hey you did something wrong...",
        "code": 0
    }
}
```

###Message example
```php
return Api::error('Something is wrong!!!', 1000, 505);
```

####Response sample:
```json
{
    "status": 505,
    "error": {
        "message": "Something is wrong!!!",
        "code": 1000
    }
}
```
