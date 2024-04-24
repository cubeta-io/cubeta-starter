import { Editor } from "@tinymce/tinymce-react";
import React, { HTMLProps, useRef, useState } from "react";
import { usePage } from "@inertiajs/react";

export interface TextEditorProps extends HTMLProps<HTMLTextAreaElement> {
    name: string;
    label?: string;
    defaultValue?: string | any;
    className?: string;
    onEditorChange?: (value: string) => void;
    onChange?: (e: React.FormEvent<HTMLTextAreaElement>) => void;
    onInput?: (e: React.FormEvent<HTMLTextAreaElement>) => void;
}

const TextEditor: React.FC<TextEditorProps> = ({
    label,
    className,
    onEditorChange,
    name,
    defaultValue,
    onChange = undefined,
    onInput = undefined,
    ...props
}) => {
    const errors = usePage().props.errors;
    const error = name && errors[name] ? errors[name] : undefined;

    const textRef = useRef<HTMLTextAreaElement>(null);
    const [value, setValue] = useState(defaultValue ?? "");
    return (
        <div className={className ?? ""}>
            <label>
                {label}
                <textarea
                    ref={textRef}
                    value={value}
                    className={"hidden"}
                    hidden={true}
                    onInput={(e) => {
                        if (onChange) {
                            onChange(e);
                        } else if (onInput) {
                            onInput(e);
                        }
                    }}
                    {...props}
                    name={name ?? ""}
                />
                <Editor
                    apiKey="y4iv5cecmr9d6dcukpwqpuiedu0mohczn48fm1pdzxm7kyi1"
                    init={{
                        plugins:
                            "anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount checklist mediaembed casechange export formatpainter pageembed linkchecker a11ychecker tinymcespellchecker permanentpen powerpaste advtable advcode editimage advtemplate mentions tableofcontents footnotes mergetags autocorrect typography inlinecss markdown",
                        toolbar:
                            "undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat",
                    }}
                    initialValue={defaultValue}
                    onEditorChange={(e) => {
                        if (onEditorChange) {
                            onEditorChange(e);
                        }
                        setValue(e);
                        textRef?.current?.dispatchEvent(
                            new Event("input", { bubbles: true })
                        );
                    }}
                />
            </label>
            {error ? <p className={"text-red-700 text-sm"}>{error}</p> : ""}
        </div>
    );
};

export default TextEditor;
