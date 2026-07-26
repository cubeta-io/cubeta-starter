import {
  Select as ShadcnSelect,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { toTitleCase } from "@/helper";
import { usePage } from "@inertiajs/react";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";

type SelectOption<T extends string> = {
  label: string;
  value: T;
};

type SelectData<T extends string> = readonly T[] | readonly SelectOption<T>[];

type SelectProps<T extends string> = {
  data: SelectData<T>;
  selected?: T;
  label?: string;
  placeholder?: string;
  onChange?: (value: T) => void;
  name?: string;
};

const isOptionObject = <T extends string>(
  item: T | SelectOption<T>,
): item is SelectOption<T> => {
  return typeof item === "object" && item !== null;
};

const normalizeOptions = <T extends string>(
  data: SelectData<T>,
): SelectOption<T>[] => {
  return data.map((item) =>
    isOptionObject(item)
      ? item
      : {
          label: toTitleCase(item),
          value: item,
        },
  );
};

const Select = <T extends string>({
  data,
  selected,
  label,
  placeholder = "Select an item",
  onChange,
  name,
}: SelectProps<T>) => {
  const errors = usePage().props.errors as Record<string, string>;
  const options = normalizeOptions(data);

  return (
    <Field>
      {label && <FieldLabel>{label}</FieldLabel>}
      <ShadcnSelect
        defaultValue={selected}
        onValueChange={(value) => onChange?.(value as T)}
      >
        <SelectTrigger className="w-full">
          <SelectValue placeholder={placeholder} />
        </SelectTrigger>
        <SelectContent>
          <SelectGroup>
            {options.map((option) => (
              <SelectItem key={option.value} value={option.value}>
                {option.label}
              </SelectItem>
            ))}
          </SelectGroup>
        </SelectContent>
      </ShadcnSelect>
      {name && errors[name] && <FieldError>{errors[name]}</FieldError>}
    </Field>
  );
};

export default Select;
