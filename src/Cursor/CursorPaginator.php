<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\CursorPagination\Cursor;

use Countable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\Paginator;
use IteratorAggregate;
use LaravelJsonApi\Contracts\Schema\ID;
use LaravelJsonApi\Core\Schema\IdParser;
use LogicException;
use Traversable;

class CursorPaginator implements IteratorAggregate, Countable
{

    /**
     * @var EloquentCollection
     */
    private EloquentCollection $items;

    /**
     * @var bool
     */
    private bool $more;

    /**
     * @var Cursor
     */
    private Cursor $cursor;

    /**
     * @var string
     */
    private string $key;

    /**
     * @var string|null
     */
    private ?string $path;

    /**
     * @var ID|null
     */
    private ?ID $id = null;

    /**
     * CursorPaginator constructor.
     *
     * @param EloquentCollection $items
     * @param bool $more
     *      whether there are more items.
     * @param Cursor $cursor
     * @param string $key
     *      the key used for the after/before identifiers.
     */
    public function __construct(EloquentCollection $items, bool $more, Cursor $cursor, string $key)
    {
        $this->more = $more;
        $this->items = $items;
        $this->cursor = $cursor;
        $this->key = $key;
    }

    /**
     * @param ID|null $id
     * @return $this
     */
    public function withIdField(?ID $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return EloquentCollection
     */
    public function getItems(): EloquentCollection
    {
        return $this->items;
    }

    /**
     * @return int|string|null
     */
    public function firstItem()
    {
        if ($first = $this->items->first()) {
            return IdParser::make($this->id)->encode(
                $first->{$this->key},
            );
        }

        return null;
    }

    /**
     * @return int|string|null
     */
    public function lastItem()
    {
        if ($last = $this->items->last()) {
            return IdParser::make($this->id)->encode(
                $last->{$this->key},
            );
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasMorePages(): bool
    {
        return $this->more;
    }

    /**
     * @return bool
     */
    public function hasNoMorePages(): bool
    {
        return !$this->hasMorePages();
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        if ($limit = $this->cursor->getLimit()) {
            return $limit;
        }

        throw new LogicException('Expecting a limit to have been set on the cursor.');
    }

    /**
     * @return string|null
     */
    public function getFrom(): ?string
    {
        $first = $this->firstItem();

        return $first ? (string) $first : null;
    }

    /**
     * @return string|null
     */
    public function getTo(): ?string
    {
        $last = $this->lastItem();

        return $last ? (string) $last : null;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        yield from $this->items;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->items->count();
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * @return $this
     */
    public function withCurrentPath(): self
    {
        $this->path = Paginator::resolveCurrentPath();

        return $this;
    }

    /**
     * Set the base path for paginator generated URLs.
     *
     * @param string $path
     * @return $this
     */
    public function withPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the base path for paginator generated URLs.
     *
     * @return string|null
     */
    public function path(): ?string
    {
        return $this->path;
    }

}
