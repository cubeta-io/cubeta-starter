import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import { usePage } from "@inertiajs/react";
import React from "react";
import {
  Field,
  FieldError,
  FieldLabel,
  FieldLegend,
  FieldSet,
} from "@/components/ui/field";

interface IRadioProps<VALUE extends string | number> {
  name: string;
  items: { label?: string; value: VALUE }[];
  checked?: ((value: VALUE) => boolean) | string;
  onChange?: (e: VALUE) => void;
  label?: string;
}

const Radio = <VALUE extends string | number>({
  name,
  items = [],
  checked = undefined,
  onChange = undefined,
  label = undefined,
}: IRadioProps<VALUE>) => {
  const {
    props: { errors },
  } = usePage();
  const defaultValue = checked
    ? typeof checked == "function"
      ? items?.filter((i) => checked(i.value))?.[0]?.value
      : checked
    : undefined;

  return (
    <FieldSet className={"w-full max-w-xs"}>
      {label && <FieldLegend variant={"label"}>{label}</FieldLegend>}
      <RadioGroup
        onValueChange={onChange}
        defaultValue={defaultValue?.toString()}
        id={`${name}_${label}_id`}
      >
        {items.map((i, index) => (
          <Field key={index} orientation={"horizontal"}>
            <RadioGroupItem
              value={i.value?.toString()}
              id={i.label + "_" + i.value + "_" + "_id"}
            />
            <FieldLabel
              htmlFor={i.label + "_" + i.value + "_" + "_id"}
              className={"font-normal"}
            >
              {i.label}
            </FieldLabel>
          </Field>
        ))}
      </RadioGroup>
      {errors[name] && <FieldError>{errors[name]}</FieldError>}
    </FieldSet>
  );
};

export default Radio;
