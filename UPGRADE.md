# Migration Guide

This guide is for migrating from this package's cursor pagination implementation to the [core implementation](https://laraveljsonapi.io/4.x/schemas/pagination.html#cursor-based-pagination) based on laravel's cursor pagination.

The new implementation has a very similar API so the migration should be relatively straight forward and **NON BREAKING** in most case.

> [!WARNING]
> If you have communicated to clients that they can use a resource's ID as a cursor value this will no longer be possible with the new implementation. Cursor values are now opaque strings, therefore this would be a **BREAKING CHANGE**.
> If however their implementation is driven from the cursors provided in page `meta` data or the provided pagination `links` this should be ok.

## Upgrade Steps

1. Replace use of `\LaravelJsonApi\CursorPagination\CursorPagination` with `\LaravelJsonApi\Eloquent\Pagination\CursorPagination` in your schema's pagination method.

```diff
namespace App\JsonApi\V1\Posts;

- use LaravelJsonApi\CursorPagination\CursorPagination;
+ use LaravelJsonApi\Eloquent\Pagination\CursorPagination;
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
        return CursorPagination::make(ID::make());
    }
}
```

2. Update sorting

 - Replace any use of `withCursorColumn` use with a default sort in your schema. You will need to ensure the field is `->sortable()`.

```diff
class PostSchema extends Schema
{
+    protected $defaultSort = '-publishedAt';

// ...

    /**
     * Get the resource paginator.
     *
     * @return CursorPagination
     */
    public function pagination(): CursorPagination
    {
-         return CursorPagination::make(ID::make())
-             ->withCursorColumn('published_at');
+         return CursorPagination::make(ID::make());
    }
}
```

 - If you were relying on the default created_at sort from this package you will want to add this as a default sort.
```diff
class PostSchema extends Schema
{
+    protected $defaultSort = '-createdAt';
// ...
}
```

3. Update your Form Request validation rules

 - Since the new implementation supports arbitrary sorting you may wish to update your form request validation rules to allow user sorting.
```diff
class PostCollectionQuery extends ResourceQuery
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            //...
-             'sort' => JsonApiRule::notSupported(),
+             'sort' => [
+                 'nullable',
+                 'string',
+                 JsonApiRule::sort(),
+             ],
            //...
        ];

    }
}
```
 - Since cursor value are now opaque strings you may need to update your validation rules to allow for this if you were validating them as resource ids.
```diff
class PostCollectionQuery extends ResourceQuery
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            //...
-             'page.after' => ['filled', 'string', 'exists:posts,id'],
-             'page.before' => ['filled', 'string', 'exists:posts,id'],
+             'page.after' => ['filled', 'string'],
+             'page.before' => ['filled', 'string'],
            //...
        ];

    }
}
```

4. Remove this package

```bash
composer remove laravel-json-api/cursor-pagination
```

5. Update any API documentation

Update any documentation or client facing information to reflect the changes to cursor values.
