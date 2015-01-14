
[![Latest Stable Version](https://poser.pugx.org/vinelab/api-manager/v/stable.svg)](https://packagist.org/packages/vinelab/api-manager) 
[![Total Downloads](https://poser.pugx.org/vinelab/api-manager/downloads.svg)](https://packagist.org/packages/vinelab/api-manager) 
[![Latest Unstable Version](https://poser.pugx.org/vinelab/api-manager/v/unstable.svg)](https://packagist.org/packages/vinelab/api-manager) 
[![License](https://poser.pugx.org/vinelab/api-manager/license.svg)](https://packagist.org/packages/vinelab/api-manager)
[![Build Status](https://travis-ci.org/Vinelab/api-manager.svg)](https://travis-ci.org/Vinelab/api-manager)

# API Manager

A simple API response formatter and handler for Laravel. Beautify and unify your responses with the least effort possible.

## Install

### Via composer:
```php
{
     "require": {
         "vinelab/api-manager": "*"
     }
 }
```

Add the service provider to the `providers` array in `app/config/app.php`:

```php
'providers' => array(
    ...
    'Vinelab\Api\ApiServiceProvider',

),
```

## Configuration

Publish the package config file:
```bash
php artisan config:publish vinelab/api-manager
```

It is now located at `app/config/packages/vinelab/api-manager/api.php`

The mappers is where you specify your mappers base namespace (See [Mappers Terminology](#mappers) for more on mappers)

```php
'mappers' => 'Lib\Api\Mappers\\',
```
The limit is where you set the maximum number of data to be returned with any endpoint request.

```php
'limit' => '50',
```

## Setup
This package expects to have **Mapper class** for each model you want to return via the API.

### Mappers
A Mapper is a class that transforms any supported data type *(i.e. Model)* into a suitable array for an API response
with the attributes of your choise. *Example:*

#### Mapper Class
```php
// this will force the existence of a map($data) function which is required by this package.
use Vinelab\Api\MappableTrait;

class PostMapper {
    use MappableTrait;

    public function map(Post $post)
    {
        return [
            'id'        => (int) $post->id,
            'title'     => $post->title,
            'body'      => $post->body,
            'published' => (boolean) $post->published
        ];
    }
}
```

#### Mapper Usage
```php
$post = Post::create([
    'title'     => 'Some Title',
    'body'      => 'Things and spaces',
    'published' => true
]);

$mapper = new PostMapper;
return $mapper->map($post);
```

Each mapper class must  have an implementation of a `map()` function, thus you should use the API `MappableTrait` trait.
>*traits in php acts similarly to interfaces, with the ability to override the function signature*.

here's an example of a mapper called `PostsMapper`

Note: you can override the argument data type of the `map()` function, to accept an `array` or an object of the `model`, or anything else you prefer.

### Mapper Examples

#### Array Mapping

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

#### Model Mapping
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

## Usage and Responses

From your controller you can use the `Api` Facade Class that contains these 2 important functions `respond` and `error`.

The `Api::respond` accepts different types of parameters, it can be an instance of `Illuminate\Pagination\Paginator`,
Eloquent Model, `Illuminate\Database\Eloquent\Collection` of model objects or an `array`.

> The responses returned by this package follows the conventions of a [json api](http://jsonapi.org/format/) and the
standards recommended by the book [Build APIs You Won't Hate](https://leanpub.com/build-apis-you-wont-hate).

```php
Api::respond($data, $total = null, $page = null, $status = 200, $headers = [], $options = 0)
```
> When `$total` and `$page` are `null` they won't be included in the response.

### Pagination
```php
return Api::respond('PostsMapper', Post::paginate(3));
```

#### Response

```json
{
    "status": 200,
    "total": 3,
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

### Model
```php
return Api::respond('PostsMapper',Post::first());
```

#### Response
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

### Collection
```php
$data = Post::where('active', '0')->get();
$total = $data->count();
$page = 2;

return Api::respond('PostsMapper', $data, $total, $page);
```

#### Response
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

### Data Limit
User can specify a limit `/?limit=20` for every request, to get the requested limit use `Api::limit()`, this will automatically check if the requested limit doesn't exceed your  maximum specified limit value. 

To override the limit value *(configured limit)* within your code you can use `Api::setLimit(100)`.

## Error Handling
For an error response use the `Api::error` function.

```php
Api::error($exception, $code = 0, $status = 500, $headers = [], $options = 0);
```

> `$exception` can be either a **string** (the exception message) or an inheritance of either `Exception` or `RuntimeException`

### Erorr with Exception
```php
try {
    throw WhateverCustomException('You deserve it, this is your fault.', 1001);
} catch (WhateverCustomException $e)
{
	return Api::error($e, $e->getCode(), 401);
}
```

#### Response
```json
{
    "status": 401,
    "error": {
        "code": 1001,
        "message": "You deserve it, this is your fault."
    }
}
```

### Error with Message
```php
return Api::error('Something is wrong!!!', 1000, 505);
```

#### Response
```json
{
    "status": 505,
    "error": {
        "code": 1000,
        "message": "Something is wrong!!!"
    }
}
```
## Contributing

Please see [CONTRIBUTING](https://github.com/Vinelab/api-manager/blob/master/CONTRIBUTING.md) for details.

## License
The package is open-sourced software licensed under the  [MIT license](https://github.com/Vinelab/api-manager/blob/master/LICENSE).

