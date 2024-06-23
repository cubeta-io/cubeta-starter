import React, { HTMLProps } from "react";
import { usePage } from "@inertiajs/react";

export interface TextEditorProps extends HTMLProps<HTMLTextAreaElement> {
    name: string;
    label?: string;
    className?: string;
    required?: boolean;
}

const TextEditor: React.FC<TextEditorProps> = ({
                                                   name,
                                                   label,
                                                   className,
                                                   required = false,
                                                   ...props
                                               }) => {
    const errors = usePage().props.errors;
    const error = name && errors[name] ? errors[name] : undefined;

    return (
        <div className={className ?? ""}>
            <label className={"dark:text-white"}>
                {label}
                {required ? (
                    <span className="text-red-500 text-sm">*</span>
                ) : (
                    ""
                )}
                <textarea
                    id="OrderNotes"
                    className={
                        className ??
                        "w-full rounded-lg border-gray-200 align-top shadow-sm sm:text-sm dark:bg-dark-secondary"
                    }
                    rows={4}
                    name={name ?? ""}
                    {...props}
                />
            </label>
            {error ? <p className={"text-red-700 text-sm"}>{error}</p> : ""}
        </div>
    );
};

export default TextEditor;
