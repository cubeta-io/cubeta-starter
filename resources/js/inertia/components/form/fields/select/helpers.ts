import { Option } from "@/components/form/fields/select/types";

export const isEqual = <TValue = any, TLabel = any>(
  option1: Option<TValue, TLabel>,
  option2: Option<TValue, TLabel>,
): boolean =>
  (option1.label ?? undefined) == (option2.label ?? undefined) &&
  (option1.value ?? undefined) == (option2.value ?? undefined);

export const include = <TValue = any, TLabel = any>(
  option: Option<TValue, TLabel>,
  selected: Option<TValue, TLabel>[],
): boolean => selected.filter((op) => isEqual(option, op)).length > 0;

export const isOption = <TValue = any, TLabel = any>(
  object: any,
): object is Option<TValue, TLabel> => "label" in object && "value" in object;
