<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\CursorPagination;

use InvalidArgumentException;
use LaravelJsonApi\Core\Document\Link;
use LaravelJsonApi\Core\Pagination\AbstractPage;
use LaravelJsonApi\CursorPagination\Cursor\CursorPaginator;
use Traversable;

class CursorPage extends AbstractPage
{

    /**
     * @var CursorPaginator
     */
    private CursorPaginator $paginator;

    /**
     * @var string
     */
    private string $before;

    /**
     * @var string
     */
    private string $after;

    /**
     * @var string
     */
    private string $limit;

    /**
     * Fluent constructor.
     *
     * @param CursorPaginator $paginator
     * @return CursorPage
     */
    public static function make(CursorPaginator $paginator): self
    {
        return new self($paginator);
    }

    /**
     * CursorPage constructor.
     *
     * @param CursorPaginator $paginator
     */
    public function __construct(CursorPaginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * Set the "after" parameter.
     *
     * @param string $key
     * @return $this
     */
    public function withAfterParam(string $key): self
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Expecting a non-empty string.');
        }

        $this->after = $key;

        return $this;
    }

    /**
     * Set the "before" parameter.
     *
     * @param string $key
     * @return $this
     */
    public function withBeforeParam(string $key): self
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Expecting a non-empty string.');
        }

        $this->before = $key;

        return $this;
    }

    /**
     * Set the "limit" parameter.
     *
     * @param string $key
     * @return $this
     */
    public function withLimitParam(string $key): self
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Expecting a non-empty string.');
        }

        $this->limit = $key;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function first(): ?Link
    {
        return new Link('first', $this->url([
            $this->limit => $this->paginator->getPerPage(),
        ]));
    }

    /**
     * @inheritDoc
     */
    public function prev(): ?Link
    {
        if ($this->paginator->isNotEmpty()) {
            return new Link('prev', $this->url([
                $this->before => $this->paginator->firstItem(),
                $this->limit => $this->paginator->getPerPage(),
            ]));
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function next(): ?Link
    {
        if ($this->paginator->hasMorePages()) {
            return new Link('next', $this->url([
                $this->after => $this->paginator->lastItem(),
                $this->limit => $this->paginator->getPerPage(),
            ]));
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function last(): ?Link
    {
        return null;
    }

    /**
     * @param array $page
     * @return string
     */
    public function url(array $page): string
    {
        return $this->paginator->path() . '?' . $this->stringifyQuery($page);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        yield from $this->paginator;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->paginator->count();
    }

    /**
     * @inheritDoc
     */
    protected function metaForPage(): array
    {
        return [
            'perPage' => $this->paginator->getPerPage(),
            'from' => $this->paginator->getFrom(),
            'to' => $this->paginator->getTo(),
            'hasMore' => $this->paginator->hasMorePages(),
        ];
    }
}
