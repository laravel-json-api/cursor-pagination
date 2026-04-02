# laravel-json-api/cursor-pagination

Cursor pagination for [Laravel JSON:API](https://laraveljsonapi.io) packages.

> [!IMPORTANT]
> This cursor implementation pre-dates Laravel's cursor pagination implementation and the support for this in the core package.
>
>**It is recommended that you use the core package's [cursor pagination](https://laraveljsonapi.io/4.x/schemas/pagination.html#cursor-based-pagination) implementation.**
>
> If your wish to migrate to teh core implementation please see the [Migration Guide](/UPGRADE.md).


It matches the cursor pagination implementation
in the legacy package [cloudcreativity/laravel-json-api](https://github.com/cloudcreativity/laravel-json-api).

## Installation

Install using [Composer](https://getcomposer.org)

```bash
composer require laravel-json-api/cursor-pagination
```

## Usage
Cursor-based pagination is based on the paginator being given a context as to
what results to return next. So rather than an API client saying it wants
page number 2, it instead says it wants the items in the list after the
last item it received. This is ideal for infinite scroll implementations, or
for resources where rows are regularly inserted (which would affect page
numbers if you used paged-based pagination).

Cursor-based pagination works by keeping the list in a fixed order. This means
that if you use cursor-based pagination for a resource type, you should not
support sort parameters as this can have adverse effects on the cursor
pagination.

Our implementation utilizes cursor-based pagination via the `"after"` and
`"before"` page parameters. Both parameters take an existing resource ID
value (see below) and return resources in a fixed order. By default this
fixed order is reverse chronological order (i.e. most recent first,
oldest last). The `"before"` parameter returns resources listed before the
named resource. The `"after"` parameter returns resources listed after the
named resource. If both parameters are provided, only `"before"`is used.
If neither parameter is provided, the first page of results will be returned.

| Parameter | Description |
| :--- | :--- |
| `after` | A cursor for use in pagination. `after` is a resource ID that defines your place in the list. For instance, if you make a paged request and receive 100 resources, ending with resource with id `foo`, your subsequent call can include `page[after]=foo` in order to fetch the next page of the list. |
| `before` | A cursor for use in pagination. `before` is a resource ID that defines your place in the list. For instance, if you make a paged request and receive 100 resources, starting with resource with id `bar` your subsequent call can include `page[before]=bar` in order to fetch the previous page of the list. |
| `limit` | A limit on the number of resources to be returned, i.e. the per-page amount. |

To use cursor-based pagination, return our `CursorPagination` class from your
schema's `pagination` method. For example:

```php
namespace App\JsonApi\V1\Posts;

use LaravelJsonApi\CursorPagination\CursorPagination;
use LaravelJsonApi\Eloquent\Schema;

class PostSchema extends Schema
{
    // ...

    /**
     * Get the resource paginator.
     *
     * @return CursorPagination
     */
    public function pagination(): CursorPagination
    {
        return CursorPagination::make();
    }
}
```

This means the following request:

```http
GET /api/v1/posts?page[limit]=10&page[after]=03ea3065-fe1f-476a-ade1-f16b40c19140 HTTP/1.1
Accept: application/vnd.api+json
```

Will receive a paged response:

```http
HTTP/1.1 200 OK
Content-Type: application/vnd.api+json

{
  "meta": {
    "page": {
      "from": "bfdaa836-68a3-4427-8ea3-2108dd48d4d3",
      "hasMore": true,
      "perPage": 10,
      "to": "df093f2d-f042-49b0-af77-195625119773"
    }
  },
  "links": {
    "first": "http://localhost/api/v1/posts?page[limit]=10",
    "prev": "http://localhost/api/v1/posts?page[limit]=10&page[before]=bfdaa836-68a3-4427-8ea3-2108dd48d4d3",
    "next": "http://localhost/api/v1/posts?page[limit]=10&page[after]=df093f2d-f042-49b0-af77-195625119773"
  },
  "data": [...]
}
```

:::tip
The query parameters in the above examples would be URL encoded, but are shown
without encoding for readability.
:::

### Customising the Cursor Parameters

To change the default parameters of `"limit"`, `"after"` and `"before"`, use
the `withLimitKey`, `withAfterKey` and `withBeforeKey` methods as needed.

For example:

```php
public function pagination(): CursorPagination
{
    return CursorPagination::make()
        ->withLimitKey('size')
        ->withAfterKey('starting-after')
        ->withBeforeKey('ending-before');
}
```

The client would need to send the following request:

```http
GET /api/v1/posts?page[size]=25&page[starting-after]=df093f2d-f042-49b0-af77-195625119773 HTTP/1.1
Accept: application/vnd.api+json
```

### Customising the Cursor Column

By default the cursor-based approach uses a model's created at column in
descending order for the list order. This means the most recently created
model is the first in the list, and the oldest is last. As the created at
column is not unique (there could be multiple rows created at the same time),
it uses the resource's route key column as a secondary sort order, as this
column must always be unique.

To change the column that is used for the list order use the `withCursorColumn`
method. If you prefer your list to be in ascending order, use the
`withAscending` method. For example:

```php
public function pagination(): CursorPagination
{
    return CursorPagination::make()
        ->withCursorColumn('published_at')
        ->withAscending();
}
```

### Validating Cursor Parameters

You should always validate page parameters that sent by an API client.
This is described in the [query parameters chapter.](https://laraveljsonapi.io/4.x/requests/query-parameters.md)

For the cursor-based approach, you **must** validate that the identifier
provided by the client for the `"after"` and `"before"` parameters are valid
identifiers, because invalid identifiers cause an error in the cursor.
It is also recommended that you validate the `limit` so that it is within an
acceptable range.

As the cursor relies on the list being in a fixed order (that it controls),
you **must** also disable sort parameters.

For example:

```php
namespace App\JsonApi\V1\Posts;

use LaravelJsonApi\Validation\Rule as JsonApiRule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceQuery;

class PostCollectionQuery extends ResourceQuery
{

    public function rules(): array
    {
        return [
            // ...other rules

            'sort' => JsonApiRule::notSupported(),

            'page' => [
              'nullable',
              'array',
              JsonApiRule::page(),
            ],

            'page.limit' => ['filled', 'numeric', 'between:1,100'],

            'page.after' => ['filled', 'string', 'exists:posts,id'],

            'page.before' => ['filled', 'string', 'exists:posts,id'],
        ];
    }
}
```

## License

Laravel JSON:API is open-sourced software licensed under the [MIT License](./LICENSE).
