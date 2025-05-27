import { AvailableLocales } from "@/Models/Translatable";
import User from "@/Models/User";
import { route as routeFn } from "ziggy-js";
import { PageProps as InertiaProps } from "@inertiajs/core";

export type MiddlewareProps<
// @ts-ignore
  T extends InertiaProps<string, unknown> = Record<string, unknown>,
> = T & {
  authUser: User;
  availableLocales: AvailableLocales[] | string[];
  currentLocale: AvailableLocales | string;
  currentRoute: string;
  tinymceApiKey: string;
  asset: string;
  baseUrl: string;
  message?: string;
  success?: string;
  error?: string;
};

declare global {
  var route: typeof routeFn;
}
