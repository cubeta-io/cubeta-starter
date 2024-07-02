import { usePage } from "@inertiajs/react";
import { PageProps } from "../types";

export type AvailableLocales = "en";

export type Translatable = Record<AvailableLocales, string>;

export function translate(
    val: string | undefined | null,
    returnObject?: boolean,
): string;

export function translate(
    val: string | undefined | null,
    returnObject: true,
): Translatable;

export function translate(
    val: string | undefined | null,
    returnObject = false,
): string | Translatable {
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

        const { currentLocale } = usePage<PageProps>().props;
        return tr[currentLocale];
    } catch (e) {
        if (returnObject) {
            return { en: "" } as Translatable;
        }
        return val ?? "";
    }
}
