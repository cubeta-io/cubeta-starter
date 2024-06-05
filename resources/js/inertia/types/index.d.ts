import { AvailableLocales } from "@/Models/Translatable";
import { User } from "@/Models/User";
import { route as routeFn } from "ziggy-js";

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    authUser: User;
    availableLocales: AvailableLocales[];
    currentLocale: AvailableLocales;
    currentRoute: string;
    tinymceApiKey: string;
    asset: string;
    baseUrl: string;
    csrfToken: string;
    message?: string;
    success?: string;
    error?: string;
};

declare global {
    var route: typeof routeFn;
}
