<?php declare(strict_types=1);

namespace bz\Lang_Switch;

use JetBrains\PhpStorm\Pure;

class Lang_Switch
{
    protected object $options;
    private array $request, $cookie;
    private ?string $accept_language;
    public ?string $language;
    public array $available_languages, $user_languages, $response;

    public function __construct(Lang_Switch_Options $options, ?array $request = null)
    {
        $this->options = $options;
        $this->request = $request['request'] ?? $_REQUEST;
        $this->cookie = $request['cookie'] ?? $_COOKIE;
        $this->accept_language = $request['accept_language'] ?? $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $this->available_languages = $this->getAvailableLanguages($this->options->getPath());
        $this->init();
    }

    public function getAvailableLanguages(string $folder): array
    {
        if (is_dir($folder)) {
            $lang_dirs = scandir($folder);
            $remove = array_merge(['.', '..'], $this->options->getIgnoredLocales());
            $locales = array_diff($lang_dirs, $remove);
            return array_values(array_filter($locales, function ($locale) {
                return $this->checkLocale($locale);
            }));
        }
        return [];
    }

    #[Pure] public function checkLocale(string $locale): bool
    {
        $path = $this->getPath($locale);
        return file_exists($path);
    }

    #[Pure] public function getPath(string $locale): string
    {
        return $this->options->getPath() . $locale . '/' . $this->options->getFileName();
    }

    public function init(): void
    {
        if (count($this->available_languages) > 0) {
            $this->setLanguage();
        } else {
            $this->response['error'] = 'Locales not found';
        }
    }

    private function setLanguage(): void
    {
        $param = $this->options->getParamName();

        if (isset($this->request[$param])) {
            $this->language = $this->getFromRequest($param);
            $this->response['cookie_updated'] = $this->setCookie($param);
        }

        if (isset($this->cookie[$param]) && !isset($this->language)) {
            $this->language = $this->getFromCookies($param);
        }

        if (isset($this->accept_language) && !isset($this->language)) {
            $this->language = $this->getFromUser();
        }

        if (!isset($this->language)) {
            $this->setFromAvailable();
        }

        if (isset($this->language)) {
            $this->setResponse();
        } else {
            $this->response['error'] = 'Can\'t set the language';
        }
    }

    private function getFromRequest(string $param): ?string
    {
        return $this->compareLanguages([$this->request[$param]], $this->available_languages);
    }

    private function getFromCookies(string $param): ?string
    {
        return $this->compareLanguages([$this->cookie[$param]], $this->available_languages);
    }

    private function setCookie(string $param): bool
    {
        if (isset($this->language)) {
            $_COOKIE[$param] = $this->language;
            setcookie($param, $this->language, intval(round(time() + (60 * 60 * 24 * 365.25 * 10))));
            return true;
        } else {
            return false;
        }
    }

    public static function unsetCookie(string $param): bool
    {
        if (isset($_COOKIE[$param])) {
            unset($_COOKIE[$param]);
            setcookie($param, '', -1);
            return true;
        } else {
            return false;
        }
    }

    public function getFromUser(?string $languages = null): ?string
    {
        $languages = $languages ?? $this->accept_language;
        $this->user_languages = $this->getLanguagesFromString($languages);
        return $this->compareLanguages($this->user_languages, $this->available_languages);
    }

    private function setFromAvailable(): void
    {
        $this->language = $this->getFromDefault();

        if (!isset($this->language)) {
            $this->language = $this->getFirst();
        }
    }

    #[Pure] public function getFromDefault(): ?string
    {
        return $this->compareLanguages($this->options->getDefaultLocales(), $this->available_languages);
    }

    public function getFirst(): string
    {
        return $this->available_languages[0];
    }

    private function setResponse(): void
    {
        $path = $this->getPath($this->language);
        $this->response['lang'] = $this->language;
        $this->response['file'] = $this->getFile($path);

        if (!$this->response['file']) {
            $this->response['error'] = "An error occurred while trying to get the file \"${path}\"";
        }
    }

    private function getFile(?string $path): string|false
    {
        $path = $path ?? $this->getPath($this->language);
        return file_get_contents($path);
    }

    #[Pure] public static function compareLanguages(array $languages, array $available_languages): ?string
    {
        foreach ($languages as $language) {
            if (in_array($language, $available_languages)) {
                return $language;
            }
        }
        return null;
    }

    public static function getLanguagesFromString(string $languages): array
    {
        $lang_list = array();
        if (isset($languages)) {
            $user_languages = explode(',', $languages);

            foreach ($user_languages as $lang) {
                $lang = explode(';', $lang);
                array_push($lang_list, $lang[0]);
            }

        }
        return $lang_list;
    }

    public function debug_preview(): void
    {
        print('<pre>' . print_r($this, true) . '</pre>');
    }
}