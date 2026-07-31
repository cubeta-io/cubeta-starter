export type AvailableLocales = "en";

export type Translatable = Record<AvailableLocales, string>;

export function translate(
  val: string | undefined | null,
  returnObject?: false,
): string;

export function translate(
  val: string | undefined | null,
  returnObject: true,
): Translatable;

export function translate(
  val: string | undefined | null,
  returnObject = false,
): string | Translatable {
  const currentLocale = window.localStorage.getItem("locale") ?? "en";
  try {
    if (!val && returnObject) {
      return { en: "" } as Translatable;
    } else if (!val && !returnObject) {
      return "";
    }
    const tr = JSON.parse(val ?? "{}");
    if (returnObject) {
      return tr;
    }

    return tr[currentLocale];
  } catch (e) {
    if (returnObject) {
      return { en: "" } as Translatable;
    }
    return val ?? "";
  }
}
