<?php

namespace App\Broadcasting;

use Illuminate\Support\Manager;
use App\Broadcasting\Drivers\RedisDriver;
use App\Broadcasting\Drivers\PusherDriver;
use App\Broadcasting\Drivers\SocketIoDriver;

class BroadcastManager extends Manager
{
    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('broadcasting.default', 'redis');
    }

    /**
     * Create the Redis broadcast driver.
     *
     * @return \App\Broadcasting\Drivers\RedisDriver
     */
    protected function createRedisDriver()
    {
        return new RedisDriver(
            $this->app['redis'],
            $this->app['config']['broadcasting.connections.redis']
        );
    }

    /**
     * Create the Socket.IO broadcast driver.
     *
     * @return \App\Broadcasting\Drivers\SocketIoDriver
     */
    protected function createSocketIoDriver()
    {
        return new SocketIoDriver(
            $this->app['config']['broadcasting.connections.socket_io']
        );
    }

    /**
     * Create the Pusher broadcast driver.
     *
     * @return \App\Broadcasting\Drivers\PusherDriver
     */
    protected function createPusherDriver()
    {
        return new PusherDriver(
            $this->app['config']['broadcasting.connections.pusher']
        );
    }

    /**
     * Get the subscriber count for a channel
     *
     * @param string $channel
     * @return int
     */
    public function getSubscriberCount(string $channel): int
    {
        return $this->driver()->getSubscriberCount($channel);
    }

    /**
     * Get the subscribers for a channel
     *
     * @param string $channel
     * @return array
     */
    public function getSubscribers(string $channel): array
    {
        return $this->driver()->getSubscribers($channel);
    }
}
