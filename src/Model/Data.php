<?php

namespace App\Model;

final class Data
{
    protected ?string $url = null;

    protected array $props = [];

    public function __construct(?string $url = null)
    {
        if (null !== $url) {
            $this->url = $url;
        }
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getProps(): array
    {
        return $this->props;
    }

    public function setProps(array $props): self
    {
        $this->props = $props;

        return $this;
    }

    public function get(string $key): mixed
    {
        if (!array_key_exists($key, $this->props)) {
            throw new \Exception(sprintf('Значение с %s ключом не найдено', $key));
        }

        return $this->props[$key];
    }

    public function set(string $key, $value): self
    {
        $this->props[$key] = $value;

        return $this;
    }

    public function add(string $key, mixed $value): void
    {
        if (array_key_exists($key, $this->props)) {
            throw new \Exception(sprintf('Значение с %s ключом уже установлено', $key));
        }

        $this->props[$key] = $value;
    }

    public function toArray(): array
    {
        return array_merge(['hash' => sha1($this->url), 'url' => $this->url], $this->props);
    }

    static public function fromArray(array $values): self
    {
        if (!isset($values['url'])) {
            throw new \Exception('Значение URL не найдено');
        }

        $url = $values['url'];
        unset($values['url']);

        return (new self($url))->setProps($values);
    }
}
