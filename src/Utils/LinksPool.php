<?php

namespace App\Utils;

/**
 * Очередь ссылок на обработку
 */
class LinksPool
{
    protected array $pool = [];

    /**
     * Вставить ссылку в конец очереди
     *
     * @param string $url
     */
    public function add(string $url, array $options = []): self
    {
        $this->pool[sha1($url)] = ['url' => $url, 'options' => $options];

        return $this;
    }

    /**
     * Вставить ссылку в начало очереди
     *
     * @param string $url
     */
    public function insert(string $url, array $options = []): self
    {
        $this->pool = [sha1($url) => ['url' => $url, 'options' => $options]] + $this->pool;

        return $this;
    }

    /**
     * * Получить первую ссылку из очереди
     *
     * @return array
     */
    public function get(): array
    {
        return array_shift($this->pool);
    }

    /**
     * Удалить из очереди значение
     *
     * @param string $url
     * @return $this
     */
    public function remove(string $url): self
    {
        unset($this->pool[sha1($url)]);

        return $this;
    }

    /**
     * Очистить очередь
     *
     * @return $this
     */
    public function clear(): self
    {
        $this->pool = [];

        return $this;
    }

    /**
     * Количество ссылок в очереди
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->pool);
    }
}
