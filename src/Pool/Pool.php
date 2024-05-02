<?php

namespace App\Pool;

/**
 * Очередь ссылок на обработку
 */
class Pool
{
    protected array $queue = [];

    protected array $processed = [];

    /**
     * Получить первый URL из очереди
     */
    public function get(): string
    {
        $url = array_shift($this->queue);

        $this->processed[] = $url;

        return $url;
    }

    /**
     * вставить URL в начало очереди
     */
    public function insert(string $url): bool
    {
        if (in_array($url, $this->processed)) {
            return false;
        }

        array_unshift($this->queue, $url);

        return true;
    }

    /**
     * добавить URL в конец очереди
     */
    public function add(string $url): bool
    {
        if (in_array($url, $this->queue) || in_array($url, $this->processed)) {
            return false;
        }

        array_push($this->queue, $url);

        return true;
    }

    public function skip(array $urls): void
    {
        $filtered = array_filter($urls, fn($url) => !in_array($url, $this->processed));
        $this->processed = array_merge($this->processed, $filtered);
    }


    public function clear(): void
    {
        $this->queue = [];
    }

    public function length(): int
    {
        return count($this->queue);
    }

    public function countProcessed(): int
    {
        return count($this->processed);
    }
}
