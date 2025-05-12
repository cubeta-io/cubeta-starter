import React from "react";
import { usePage } from "@inertiajs/react";

interface IRadioProps {
  name: string;
  items: { label?: string; value: any }[];
  checked?: ((value: any) => boolean) | any;
  onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void;
  label?: string;
}

const Radio: React.FC<IRadioProps> = ({
  name,
  items = [],
  checked = undefined,
  onChange = undefined,
  label = undefined,
}) => {
  const errors = usePage().props.errors;
  const error = name && errors[name] ? errors[name] : undefined;

  return (
    <label
      className={"flex flex-col items-start justify-between dark:text-white"}
    >
      {label ?? ""}
      <div className="flex w-full flex-wrap gap-2">
        {items.map((item, index) => {
          let isChecked = false;
          if (checked !== undefined) {
            if (typeof checked == "function") {
              isChecked = checked(item.value);
            } else {
              isChecked = item.value == checked;
            }
          }

          return (
            <div key={index} className="flex items-center gap-2">
              <label className="ms-2 font-medium dark:text-white">
                {item?.label}
              </label>
              <input
                type="radio"
                value={item.value}
                defaultChecked={isChecked}
                name={name}
                onChange={onChange ? (e) => onChange(e) : undefined}
              />
            </div>
          );
        })}
      </div>
      {error ? <p className={"text-sm text-red-700"}>{error}</p> : ""}
    </label>
  );
};

export default Radio;
