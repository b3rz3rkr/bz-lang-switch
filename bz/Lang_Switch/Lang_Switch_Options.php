<?php declare(strict_types=1);

namespace bz\Lang_Switch;

use JetBrains\PhpStorm\Pure;

class Lang_Switch_Options
{
    private string $file_name, $path, $param_name;
    private array|string $default_locales, $ignored_locales;

    public function __construct(array|null $config = null)
    {
        $this->file_name = $config['file_name'] ?? 'index.html';
        $this->path = $config['path'] ?? 'locales/';
        $this->param_name = $config['param_name'] ?? 'lang';
        $this->default_locales = $config['default_locales'] ?? ['en', 'en-US'];
        $this->ignored_locales = $config['ignored_locales'] ?? [];
    }

    public function getFileName(): string
    {
        return $this->file_name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getParamName(): string
    {
        return $this->param_name;
    }

    #[Pure] public function getDefaultLocales(): array
    {
        if (gettype($this->default_locales) === 'array')
            return $this->default_locales;
        return [$this->default_locales];
    }

    #[Pure] public function getIgnoredLocales(): array
    {
        if (gettype($this->ignored_locales) === 'array')
            return $this->ignored_locales;
        return [$this->ignored_locales];
    }
}