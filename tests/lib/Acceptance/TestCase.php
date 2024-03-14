<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\CursorPagination\Tests\Acceptance;

use Illuminate\Foundation\Testing\Concerns\InteractsWithDeprecationHandling;
use LaravelJsonApi\Contracts\Schema\Container as SchemaContainerContract;
use LaravelJsonApi\Contracts\Server\Server;
use LaravelJsonApi\Core\Schema\Container as SchemaContainer;
use LaravelJsonApi\Core\Support\ContainerResolver;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use InteractsWithDeprecationHandling;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutDeprecationHandling();

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->app->singleton(SchemaContainerContract::class, static function ($container) {
            $resolver = new ContainerResolver(static fn () => $container);
            return new SchemaContainer($resolver, $container->make(Server::class), [
                \App\VideoSchema::class,
            ]);
        });

        $this->app->singleton(Server::class, function () {
            $server = $this->createMock(Server::class);
            $server->method('schemas')->willReturnCallback(fn() => $this->schemas());
            return $server;
        });
    }

    /**
     * @return SchemaContainerContract
     */
    protected function schemas(): SchemaContainerContract
    {
        return $this->app->make(SchemaContainerContract::class);
    }

    /**
     * @return Server
     */
    protected function server(): Server
    {
        return $this->app->make(Server::class);
    }
}
