<?php

namespace App\Traits;

trait HasTranslations
{
    /**
     * Transparently resolve translatable attributes to the current app
     * locale, falling back to the default language, then to whatever
     * translation exists. Non-translatable attributes behave as normal.
     */
    public function getAttribute($key)
    {
        if (in_array($key, $this->translatable ?? [], true)) {
            return $this->getTranslation($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * Encode an array value (e.g. ['ar' => .., 'en' => .., 'tr' => ..]
     * posted by a tabbed admin form) into the JSON column for a
     * translatable attribute. Non-array values pass through untouched.
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->translatable ?? [], true) && is_array($value)) {
            // JSON_INVALID_UTF8_SUBSTITUTE guards against json_encode() silently
            // returning false (which would otherwise get stored as the string "0")
            // when a submitted value contains malformed multi-byte sequences.
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
            $value = $encoded !== false ? $encoded : json_encode((object) []);
        }

        return parent::setAttribute($key, $value);
    }

    public function getTranslation(string $field, ?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $all = $this->translationsFor($field);
        $default = config('app.fallback_locale', 'ar');

        // An empty string means "not translated yet" (e.g. an optional language
        // tab left blank on an add/edit form), not "translated to nothing" — so
        // it must fall through to the next candidate, same as a missing key.
        foreach ([$locale, $default] as $candidate) {
            if (($all[$candidate] ?? '') !== '') {
                return $all[$candidate];
            }
        }

        foreach ($all as $value) {
            if (($value ?? '') !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * All available translations for a field, keyed by language code.
     * Used by admin edit forms to prefill each language tab.
     */
    public function translationsFor(string $field): array
    {
        $raw = $this->attributes[$field] ?? null;

        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
