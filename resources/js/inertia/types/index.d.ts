import { AvailableLocales } from "@/Models/Translatable";
import { User } from "@/Models/User";

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>
> = T & {
    authUser: User;
    availableLocales: AvailableLocales[];
    currentLocale: AvailableLocales;
    currentRoute: string;
    tinymceApiKey: string;
    asset: string;
    baseUrl: string;
};
