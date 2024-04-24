import { LocaleContext } from "@/Contexts/TrannslatableInputsContext";
import { usePage } from "@inertiajs/react";
import React, { FormEvent, useContext, useRef, useState } from "react";
import Input, { InputProps } from "./Input";
import { PageProps } from "@/types";
import { Translatable, translate } from "@/Models/Translatable";

interface ITranslatableInputProps extends InputProps {
    defaultValue?: string | Translatable;
    onChange?: (e: FormEvent<HTMLInputElement>) => void;
    onInput?: (e: FormEvent<HTMLInputElement>) => void;
    error?: string;
}

const TranslatableInput: React.FC<ITranslatableInputProps> = ({
    name,
    label,
    type,
    defaultValue,
    className,
    placeholder = "",
    onChange = undefined,
    onInput = undefined,
    error = undefined,
    ...props
}) => {
    const locale = useContext(LocaleContext);
    const availableLocales = usePage<PageProps>().props.availableLocales;
    const inputRef = useRef<HTMLInputElement>(null);

    if (typeof defaultValue == "string") {
        defaultValue = translate(defaultValue, true);
    }

    const [value, setValue] = useState<object | undefined>(
        defaultValue ?? undefined,
    );

    return (
        <div className={"flex flex-col w-full"}>
            <input
                ref={inputRef}
                value={JSON.stringify(value ?? {})}
                readOnly={true}
                className={"hidden"}
                onInput={(e) => {
                    if (onChange) {
                        onChange(e);
                    } else if (onInput) {
                        onInput(e);
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
                            onInput={(e) => {
                                setValue((prev) =>
                                    prev
                                        ? {
                                              ...prev,
                                              [lang]: e.target.value,
                                          }
                                        : { [lang]: e.target.value },
                                );
                                inputRef?.current?.dispatchEvent(
                                    new Event("input", { bubbles: true }),
                                );
                            }}
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
