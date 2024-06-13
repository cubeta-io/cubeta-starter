import React, {
    ChangeEvent,
    HTMLProps,
    useContext,
    useRef,
    useState,
} from "react";
import { Translatable, translate } from "@/Models/Translatable";
import { usePage } from "@inertiajs/react";
import { PageProps } from "@/types";
import { LocaleContext } from "@/Contexts/TranslatableInputsContext";

interface TranslatableProps
    extends Omit<HTMLProps<HTMLTextAreaElement>, "defaultValue"> {
    defaultValue: string | object | Translatable | undefined;
}

const TranslatableTextEditor: React.FC<TranslatableProps> = ({
                                                                 label,
                                                                 className,
                                                                 defaultValue,
                                                                 onChange = undefined,
                                                                 onInput = undefined,
                                                                 name,
                                                                 required = false,
                                                                 ...props
                                                             }) => {
    const errors = usePage().props.errors;
    const error = name && errors[name] ? errors[name] : undefined;

    if (typeof defaultValue == "string") {
        defaultValue = translate(defaultValue, true);
    }

    const textRef = useRef<HTMLTextAreaElement>(null);

    const [value, setValue] = useState<object | undefined>(defaultValue ?? {});

    const locale = useContext(LocaleContext);
    const { availableLocales } = usePage<PageProps>().props;

    const handleChange = async (
        lang: string,
        e: ChangeEvent<HTMLTextAreaElement>,
    ) => {
        await setValue((prev) =>
            prev
                ? {
                    ...prev,
                    [lang]: e.target.value,
                }
                : { [lang]: e.target.value },
        );

        if (textRef.current) {
            textRef.current.dispatchEvent(
                new Event("input", {
                    bubbles: true,
                }),
            );
        }
    };

    return (
        <div className={className ?? ""}>
            <textarea
                ref={textRef}
                name={name}
                value={JSON.stringify(value ?? "{}")}
                onInput={(e) => {
                    if (onChange) {
                        onChange(e as ChangeEvent<HTMLTextAreaElement>);
                    } else if (onInput) {
                        onInput(e as ChangeEvent<HTMLTextAreaElement>);
                    }
                }}
                className={"hidden"}
                hidden={true}
                readOnly={true}
                {...props}
            />
            {availableLocales.map((lang, index) => (
                <label
                    key={index}
                    className={lang !== locale ? "hidden" : " dark:text-white"}
                >
                    {label && `${label} - ${lang.toUpperCase()}`}
                    {required ? (
                        <span className="text-red-500 text-sm">*</span>
                    ) : (
                        ""
                    )}
                    <textarea
                        className={
                            className ??
                            " w-full rounded-lg border-gray-200 align-top shadow-sm sm:text-sm dark:bg-dark-secondary"
                        }
                        rows={4}
                        name={`${name}[${lang}]`}
                        defaultValue={defaultValue ? defaultValue[lang] : ""}
                        onChange={(e) => handleChange(lang, e)}
                        required={required}
                        {...props}
                    />
                </label>
            ))}
            {error ? <p className={"text-red-700 text-sm"}>{error}</p> : ""}
        </div>
    );
};

export default TranslatableTextEditor;
