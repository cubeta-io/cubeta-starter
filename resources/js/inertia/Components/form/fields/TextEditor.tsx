import { Editor } from "@tinymce/tinymce-react";
import React, { ChangeEvent, HTMLProps, useRef, useState } from "react";
import { usePage } from "@inertiajs/react";

export interface TextEditorProps extends HTMLProps<HTMLTextAreaElement> {
  name: string;
  label?: string;
  defaultValue?: string | any;
  className?: string;
  onEditorChange?: (value: string) => void;
  onChange?: (e: React.ChangeEvent<HTMLTextAreaElement>) => void;
  onInput?: (e: React.ChangeEvent<HTMLTextAreaElement>) => void;
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
              onChange(e as ChangeEvent<HTMLTextAreaElement>);
            } else if (onInput) {
              onInput(e as ChangeEvent<HTMLTextAreaElement>);
            }
          }}
          {...props}
          name={name ?? ""}
        />
        <Editor
            apiKey="lk5i4b6fbja20ugtpiziehibs8p7p9qrcfsgjr186ah73u63"
            init={{
                plugins:
                    "anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount linkchecker",
                toolbar:
                    "undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat",
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
