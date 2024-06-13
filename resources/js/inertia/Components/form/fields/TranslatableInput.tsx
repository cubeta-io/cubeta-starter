import {LocaleContext} from "@/Contexts/TranslatableInputsContext";
import {usePage} from "@inertiajs/react";
import React, {ChangeEvent, useContext, useRef, useState} from "react";
import Input, {InputProps} from "./Input";
import {PageProps} from "@/types";
import {Translatable, translate} from "@/Models/Translatable";

interface ITranslatableInputProps {
    defaultValue?: string | Translatable | object | undefined;
    onChange?: (e: ChangeEvent<HTMLInputElement>) => void;
    onInput?: (e: ChangeEvent<HTMLInputElement>) => void;
    required?: boolean;
}

const TranslatableInput: React.FC<
    Omit<InputProps, "defaultValue" | "onChange" | "onInput"> &
    ITranslatableInputProps
> = ({
         name,
         label,
         type,
         defaultValue,
         className,
         placeholder = "",
         onChange = undefined,
         onInput = undefined,
         required = false,
         ...props
     }) => {
    const locale = useContext(LocaleContext);
    const availableLocales = usePage<PageProps>().props.availableLocales;
    const inputRef = useRef<HTMLInputElement>(null);

    const errors = usePage().props.errors;
    const error = name && errors[name] ? errors[name] : undefined;

    if (typeof defaultValue == "string") {
        defaultValue = translate(defaultValue, true);
    }

    const [value, setValue] = useState<object | undefined | Translatable>(
        defaultValue ?? undefined,
    );

    const handleChange = async (
        e: ChangeEvent<HTMLInputElement>,
        lang: string | keyof Translatable,
    ) => {
        await setValue((prev) =>
            prev
                ? {
                    ...prev,
                    [lang]: e.target.value,
                }
                : {[lang]: e.target.value},
        );
        inputRef?.current?.dispatchEvent(new Event("input", {bubbles: true}));
    };

    return (
        <div className={"flex flex-col w-full"}>
            <input
                ref={inputRef}
                value={JSON.stringify(value ?? {})}
                readOnly={true}
                className={"hidden"}
                onInput={(e) => {
                    if (onChange) {
                        onChange(e as ChangeEvent<HTMLInputElement>);
                    } else if (onInput) {
                        onInput(e as ChangeEvent<HTMLInputElement>);
                    }
                }}
            />
            {availableLocales.map((lang: keyof Translatable, index) => {
                return (
                    <div
                        key={index}
                        className={locale != lang ? "hidden" : undefined}
                    >
                        <Input
                            name={`${name}[${lang}]`}
                            label={`${label} - ${lang.toUpperCase()}`}
                            defaultValue={
                                defaultValue ? defaultValue[lang] : ""
                            }
                            type={"text"}
                            placeholder={placeholder}
                            onInput={(e) => handleChange(e, lang)}
                            required={required}
                            {...props}
                        />
                    </div>
                );
            })}
            {error ? <p className={"text-red-700 text-sm"}>{error}</p> : ""}
        </div>
    );
};

export default TranslatableInput;
