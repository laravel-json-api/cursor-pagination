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

use InvalidArgumentException;

class Cursor
{

    /**
     * @var string|null
     */
    private ?string $before;

    /**
     * @var string|null
     */
    private ?string $after;

    /**
     * @var int|null
     */
    private ?int $limit;

    /**
     * Cursor constructor.
     *
     * @param string|null $before
     * @param string|null $after
     * @param int|null $limit
     */
    public function __construct(?string $before = null, ?string $after = null, ?int $limit = null)
    {
        if (is_int($limit) && 1 > $limit) {
            throw new InvalidArgumentException('Expecting a limit that is 1 or greater.');
        }

        $this->before = $before ?: null;
        $this->after = $after ?: null;
        $this->limit = $limit;
    }

    /**
     * @return bool
     */
    public function isBefore(): bool
    {
        return !is_null($this->before);
    }

    /**
     * @return string|null
     */
    public function getBefore(): ?string
    {
        return $this->before;
    }

    /**
     * @return bool
     */
    public function isAfter(): bool
    {
        return !is_null($this->after) && !$this->isBefore();
    }

    /**
     * @return string|null
     */
    public function getAfter(): ?string
    {
        return $this->after;
    }

    /**
     * Set a limit, if no limit is set on the cursor.
     *
     * @param int $limit
     * @return Cursor
     */
    public function withDefaultLimit(int $limit): self
    {
        if (is_null($this->limit)) {
            $copy = clone $this;
            $copy->limit = $limit;
            return $copy;
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }
}
