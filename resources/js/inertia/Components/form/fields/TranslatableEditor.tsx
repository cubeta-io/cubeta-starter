import { Editor } from "@tinymce/tinymce-react";
import React, { HTMLProps, useContext, useRef, useState } from "react";
import { Translatable, translate } from "@/Models/Translatable";
import { usePage } from "@inertiajs/react";
import { PageProps } from "@/types";
import { LocaleContext } from "@/Contexts/TrannslatableInputsContext";

interface ITranslatableEditorProps extends HTMLProps<HTMLTextAreaElement> {
    name: string;
    label?: string;
    className?: string;
    defaultValue?: string | Translatable;
    onEditorChange?: (name: string, value: any) => void;
    onChange?: (e: React.FormEvent<HTMLTextAreaElement>) => void;
    onInput?: (e: React.FormEvent<HTMLTextAreaElement>) => void;
}

const TranslatableTextEditor: React.FC<ITranslatableEditorProps> = ({
    label,
    className,
    onEditorChange,
    defaultValue,
    onChange = undefined,
    onInput = undefined,
    name,
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
                        onChange(e);
                    } else if (onInput) {
                        onInput(e);
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
                    <Editor
                        textareaName={`${name}[${lang}]`}
                        tagName={`${name}[${lang}]`}
                        apiKey="y4iv5cecmr9d6dcukpwqpuiedu0mohczn48fm1pdzxm7kyi1"
                        init={{
                            plugins:
                                "anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount checklist mediaembed casechange export formatpainter pageembed linkchecker a11ychecker tinymcespellchecker permanentpen powerpaste advtable advcode editimage advtemplate mentions tableofcontents footnotes mergetags autocorrect typography inlinecss markdown",
                            toolbar:
                                "undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat",
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
                                }),
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
