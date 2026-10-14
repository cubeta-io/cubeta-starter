import { usePage } from "@inertiajs/react";
import englishTranslations from "../locales/en.json";

type Replacements = Record<string, string | number>;
type Translations = Record<string, unknown>;
type TranslationModule = { default: Translations };
type TranslationKey<Value, Prefix extends string = ""> = Value extends string
  ? Prefix
  : Value extends Record<string, unknown>
    ? {
        [Key in keyof Value & string]: TranslationKey<
          Value[Key],
          Prefix extends "" ? Key : `${Prefix}.${Key}`
        >;
      }[keyof Value & string]
    : never;

export type LocalizationKey = TranslationKey<typeof englishTranslations>;

const localeModules = import.meta.glob<TranslationModule>("../locales/*.json", {
  eager: true,
});

function translationsFor(locale: string): Translations {
  return localeModules[`../locales/${locale}.json`]?.default ?? {};
}

function getTranslation(
  translations: Record<string, unknown>,
  key: string,
): unknown {
  return key.split(".").reduce<unknown>((value, segment) => {
    if (!value || typeof value !== "object") {
      return undefined;
    }

    return (value as Record<string, unknown>)[segment];
  }, translations);
}

export function useTranslation() {
  const { currentLocale } = usePage().props;
  const fallbackTranslations: Translations = englishTranslations;
  const translations = translationsFor(currentLocale);

  const t = (key: LocalizationKey, replacements: Replacements = {}): string => {
    const translated =
      getTranslation(translations, key) ??
      getTranslation(fallbackTranslations, key);
    const value = typeof translated === "string" ? translated : key;

    return Object.entries(replacements).reduce(
      (result, [name, replacement]) =>
        result.replaceAll(`:${name}`, String(replacement)),
      value,
    );
  };

  return { t };
}
