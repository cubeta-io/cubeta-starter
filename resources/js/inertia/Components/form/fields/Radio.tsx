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
            className={
                "flex justify-between flex-col items-start dark:text-white"
            }
        >
            {label ?? ""}
            <div className="flex flex-wrap gap-2 w-full">
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
                            <label className="font-medium dark:text-white ms-2">
                                {item?.label}
                            </label>
                            <input
                                type="radio"
                                value={item.value}
                                className="border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 w-4 h-4 text-primary focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2"
                                defaultChecked={isChecked}
                                name={name}
                                onChange={
                                    onChange ? (e) => onChange(e) : undefined
                                }
                            />
                        </div>
                    );
                })}
            </div>
            {error ? <p className={"text-red-700 text-sm"}>{error}</p> : ""}
        </label>
    );
};

export default Radio;
