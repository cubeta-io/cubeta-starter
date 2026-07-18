import "@inertiajs/core";
import User from "@/models/user";
import { route as routeFn } from "ziggy-js";
import { AvailableLocales } from "@/models/translatable";

declare module "@inertiajs/core" {
  export interface InertiaConfig {
    sharedPageProps: {
      availableLocales: AvailableLocales[];
      currentLocale: AvailableLocales;
      authUser?: User;
      asset: string;
      baseUrl: string;
    };
    flashDataType: {
      error?: string;
      success?: string;
    };
    errorValueType: string[] | string;
  }
}

declare global {
  var route: typeof routeFn;
}
