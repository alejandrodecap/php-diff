<?php

declare(strict_types=1);

namespace Jfcherng\Diff\Utility;

use StringTemplate\Engine as StringTemplateEngine;

final class Language
{
    /**
     * @var \StringTemplate\Engine
     */
    private $stringEngine;

    /**
     * @var string[] the translation dict
     */
    private $translations = [];

    /**
     * @var string the language name
     */
    private $language = '_custom_';

    /**
     * The constructor.
     *
     * @param string|string[] $target the language string or translations dict
     */
    public function __construct($target = 'eng')
    {
        $this->stringEngine = new StringTemplateEngine('{', '}');
        $this->setLanguageOrTranslations($target);
    }

    /**
     * Set up this class.
     *
     * @param string|string[] $target the language string or translations array
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public function setLanguageOrTranslations($target): self
    {
        if (\is_string($target)) {
            $this->setUpWithLanguage($target);

            return $this;
        }

        if (\is_array($target)) {
            $this->setUpWithTranslations($target);

            return $this;
        }

        throw new \InvalidArgumentException('$target must be the type of string|string[]');
    }

    /**
     * Get the language.
     *
     * @return string the language
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * Get the translations.
     *
     * @return array the translations
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    /**
     * Get the translations from the language file.
     *
     * @param string $language the language
     *
     * @throws \Exception        fail to decode the JSON file
     * @throws \LogicException   path is a directory
     * @throws \RuntimeException path cannot be opened
     *
     * @return string[]
     */
    public static function getTranslationsByLanguage(string $language): array
    {
        $filePath = __DIR__ . "/../languages/{$language}.json";
        $file = new \SplFileObject($filePath, 'r');
        $fileContent = $file->fread($file->getSize());

        /** @todo PHP ^7.3 JSON_THROW_ON_ERROR */
        $decoded = \json_decode($fileContent, true);

        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw new \Exception(\sprintf(
                'Fail to decode JSON file (code %d): %s',
                \json_last_error(),
                \realpath($filePath)
            ));
        }

        return (array) $decoded;
    }

    /**
     * Translation the text.
     *
     * @param string $text         the text
     * @param array  $placeholders the placeholders
     *
     * @return string
     */
    public function translate(string $text, array $placeholders = []): string
    {
        return isset($this->translations[$text])
            ? $this->stringEngine->render($this->translations[$text], $placeholders)
            : "![{$text}]";
    }

    /**
     * Set up this class by language name.
     *
     * @param string $language the language name
     *
     * @return self
     */
    private function setUpWithLanguage(string $language): self
    {
        return $this->setUpWithTranslations(
            self::getTranslationsByLanguage($language),
            $language
        );
    }

    /**
     * Set up this class by translations.
     *
     * @param string[] $translations the translations dict
     * @param string   $language     the language name
     *
     * @return self
     */
    private function setUpWithTranslations(array $translations, string $language = '_custom_'): self
    {
        $this->language = $language;
        $this->translations = \array_map('strval', $translations);

        return $this;
    }
}
