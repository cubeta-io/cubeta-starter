import React from "react";
import { usePage } from "@inertiajs/react";

export interface TextEditorProps extends React.ComponentProps<"textarea"> {
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
        {required ? <span className="text-sm text-red-500">*</span> : ""}
        <textarea
          id="OrderNotes"
          className={
            className ??
            "dark:bg-dark-secondary w-full rounded-lg border-gray-200 align-top shadow-sm sm:text-sm"
          }
          rows={4}
          name={name ?? ""}
          {...props}
        />
      </label>
      {error ? <p className={"text-sm text-red-700"}>{error}</p> : ""}
    </div>
  );
};

export default TextEditor;
