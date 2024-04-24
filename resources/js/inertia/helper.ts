import { usePage } from "@inertiajs/react";
import { PageProps } from "./types";

export const asset = (path: string) => {
    if (path.startsWith("/")) {
        path = path.replace("/", "");
    }

    return `${usePage<PageProps>().props.asset}${path}`;
};

export function getNestedPropertyValue(object: any, path: string): any {
    const properties = path.split(".");
    let value = object;
    for (const property of properties) {
        if (value?.hasOwnProperty(property)) {
            value = value[`${property}`];
        } else {
            return undefined;
        }
    }
    return value;
}
