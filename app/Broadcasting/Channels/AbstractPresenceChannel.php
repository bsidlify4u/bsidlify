<?php

namespace App\Broadcasting\Channels;

use Illuminate\Broadcasting\PresenceChannel as BasePresenceChannel;
use App\Contracts\Broadcasting\AuthorizesChannelJoin;

abstract class AbstractPresenceChannel extends BasePresenceChannel implements AuthorizesChannelJoin
{
    /**
     * Channel specific data to be sent with join response
     *
     * @param mixed $user
     * @return array
     */
    abstract public function channelData($user): array;

    /**
     * Get the number of current subscribers
     *
     * @return int
     */
    public function getSubscriberCount(): int
    {
        return app('broadcast.manager')->getSubscriberCount($this->name);
    }

    /**
     * Get the list of current subscribers
     *
     * @return array
     */
    public function getSubscribers(): array
    {
        return app('broadcast.manager')->getSubscribers($this->name);
    }
}
