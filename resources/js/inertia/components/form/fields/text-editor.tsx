import TiptapEditor from "@/components/form/fields/tiptap/tiptap-editor";
import { usePage } from "@inertiajs/react";
import { Editor } from "@tiptap/react";
import React from "react";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";

export interface TextEditorProps
  extends Omit<
    React.HTMLAttributes<HTMLDivElement>,
    "onChange" | "defaultValue"
  > {
  name: string;
  label?: string;
  onChange?: (value: string) => void;
  defaultValue?: string;
  extraButtons?: (editor: Editor) => React.ReactNode;
  minHeight?: string;
  required?: boolean;
}

const TextEditor: React.FC<TextEditorProps> = ({
  name,
  label,
  onChange,
  defaultValue,
  extraButtons,
  minHeight,
  required = false,
  ...props
}) => {
  const {
    props: { errors },
  } = usePage();
  const error = name && errors[name] ? errors[name] : undefined;

  return (
    <Field>
      {label && (
        <FieldLabel htmlFor={`${name}_id`}>
          {label}
          {required && <span className="text-destructive text-sm">*</span>}
        </FieldLabel>
      )}
      <TiptapEditor
        id={`${name}_id`}
        extraButtons={extraButtons}
        onChange={onChange}
        defaultValue={defaultValue}
        minHeight={minHeight}
        {...props}
      />
      {error && <FieldError>{error}</FieldError>}
    </Field>
  );
};

export default TextEditor;
