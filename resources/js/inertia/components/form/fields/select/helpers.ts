import { Option } from "@/components/form/fields/select/types";

export const isEqual = (option1: Option, option2: Option): boolean =>
  (option1.label ?? undefined) == (option2.label ?? undefined) &&
  (option1.value ?? undefined) == (option2.value ?? undefined);

export const include = (option: Option, selected: Option[]): boolean =>
  selected.filter((op) => isEqual(op, option)).length > 0;

export const isOption = (object: any): object is Option =>
  "label" in object && "value" in object;
