import { Checkbox as ShadcnCheckbox } from "@/components/ui/checkbox";
import { usePage } from "@inertiajs/react";
import React, { useState } from "react";
import {
  Field,
  FieldError,
  FieldLabel,
  FieldLegend,
  FieldSet,
} from "@/components/ui/field";

interface ICheckboxProps {
  name: string;
  items?: { label?: string; value: string | number }[];
  checked?:
    boolean | string[] | number[] | ((value: string | number) => boolean);
  onChange?: (value: boolean | (string | number)[]) => void;
  label?: string;
  value?: string | number;
}

const Checkbox: React.FC<ICheckboxProps> = ({
  name,
  items = [],
  checked = undefined,
  onChange = undefined,
  label = undefined,
  value = "1",
}) => {
  const {
    props: { errors },
  } = usePage();

  const error = name && errors?.[name] ? errors[name] : undefined;
  const isGroup = items && items.length > 0;

  const [singleChecked, setSingleChecked] = useState(() => {
    if (typeof checked === "boolean") return checked;
    return false;
  });

  const [selectedValues, setSelectedValues] = useState<(string | number)[]>(
    () => {
      if (Array.isArray(checked)) return checked;
      if (typeof checked === "function") {
        return items
          .filter((item) => checked(item.value))
          .map((item) => item.value);
      }
      return [];
    },
  );

  const handleSingleChange = (newChecked: boolean) => {
    setSingleChecked(newChecked);
    if (onChange) {
      onChange(newChecked);
    }
  };

  const handleGroupChange = (
    itemValue: string | number,
    newChecked: boolean,
  ) => {
    const newValues = newChecked
      ? [...selectedValues, itemValue]
      : selectedValues.filter((value) => value !== itemValue);

    setSelectedValues(newValues);
    if (onChange) {
      onChange(newValues);
    }
  };

  return (
    <FieldSet className={"w-full max-w-xs"}>
      {label && isGroup && <FieldLegend variant={"label"}>{label}</FieldLegend>}
      {isGroup ? (
        <div data-slot="checkbox-group" className="flex flex-col gap-3">
          {items.map((item, index) => (
            <Field key={index} orientation={"horizontal"}>
              <ShadcnCheckbox
                id={`${name}_${item.value}_${index}_id`}
                name={name}
                value={item.value?.toString()}
                checked={selectedValues.includes(item.value)}
                onCheckedChange={(newChecked) =>
                  handleGroupChange(item.value, newChecked)
                }
              />
              <FieldLabel
                htmlFor={`${name}_${item.value}_${index}_id`}
                className={"font-normal"}
              >
                {item.label}
              </FieldLabel>
            </Field>
          ))}
        </div>
      ) : (
        <Field orientation={"horizontal"}>
          <ShadcnCheckbox
            id={`${name}_id`}
            name={name}
            value={value?.toString()}
            checked={singleChecked}
            onCheckedChange={(newChecked) => handleSingleChange(newChecked)}
          />
          <FieldLabel htmlFor={`${name}_id`} className={"font-normal"}>
            {label}
          </FieldLabel>
        </Field>
      )}
      {error && <FieldError>{error}</FieldError>}
    </FieldSet>
  );
};

export default Checkbox;