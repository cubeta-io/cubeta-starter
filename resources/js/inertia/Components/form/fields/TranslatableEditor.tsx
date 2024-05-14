import { Editor } from "@tinymce/tinymce-react";
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

interface ITranslatableEditorProps {
    name: string;
    label?: string;
    className?: string;
    defaultValue?: string | Translatable;
    onEditorChange?: (name: string, value: any) => void;
    onChange?: (e: React.ChangeEvent<HTMLTextAreaElement>) => void;
    onInput?: (e: React.ChangeEvent<HTMLTextAreaElement>) => void;
    required?: boolean;
}

const TranslatableTextEditor: React.FC<
    Omit<
        HTMLProps<HTMLTextAreaElement>,
        "name" | "label" | "className" | "defaultValue" | "onChange" | "onInput"
    > &
    ITranslatableEditorProps
> = ({
         label,
         className,
         onEditorChange,
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
                <label key={index} className={lang != locale ? "hidden" : ""}>
                    {label && `${label} - ${lang.toUpperCase()}`}
                    {required ? (
                        <span className="text-red-500 text-sm">*</span>
                    ) : (
                        ""
                    )}
                    <Editor
                        textareaName={`${name}[${lang}]`}
                        tagName={`${name}[${lang}]`}
                        apiKey="lk5i4b6fbja20ugtpiziehibs8p7p9qrcfsgjr186ah73u63"
                        init={{
                            plugins:
                                "anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount linkchecker",
                            toolbar:
                                "undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat",
                        }}
                        initialValue={defaultValue ? defaultValue[lang] : ""}
                        onEditorChange={(e) => {
                            if (onEditorChange) {
                                onEditorChange(`${name}[${lang}]`, e);
                            }
                            setValue((prev) => ({
                                ...prev,
                                [lang]: e,
                            }));
                            textRef?.current?.dispatchEvent(
                                new Event("input", {
                                    bubbles: true,
                                })
                            );
                        }}
                    />
                </label>
            ))}
            {error ? <p className={"text-red-700 text-sm"}>{error}</p> : ""}
        </div>
    );
};

export default TranslatableTextEditor;
