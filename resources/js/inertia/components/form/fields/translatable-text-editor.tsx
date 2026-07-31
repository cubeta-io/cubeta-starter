import { getNestedPropertyValue } from "@/helper";
import { Translatable, translate } from "@/models/translatable";
import { usePage } from "@inertiajs/react";
import React, { useState } from "react";
import { useFormLocale } from "@/providers/form-locale-provider";
import { Field, FieldError, FieldSet } from "@/components/ui/field";
import TextEditor from "@/components/form/fields/text-editor";

interface TranslatableProps
  extends Omit<
    React.ComponentProps<typeof TextEditor>,
    "defaultValue" | "onChange"
  > {
  name: string;
  defaultValue?: string | object | Translatable | undefined;
  label?: string;
  onChange?: (v: string) => void;
}

const TranslatableTextEditor: React.FC<TranslatableProps> = ({
  label,
  defaultValue,
  onChange = undefined,
  name,
  required = false,
  ...props
}) => {
  const { locale } = useFormLocale();
  const {
    props: { availableLocales, errors },
  } = usePage();

  const error = name && errors[name] ? errors[name] : undefined;

  if (typeof defaultValue == "string") {
    defaultValue = translate(defaultValue, true);
  }

  const [value, setValue] = useState<object | undefined>(defaultValue ?? {});

  const handleChange = (
    content: string,
    lang: string | keyof Translatable,
  ) => {
    const nextValue = value
      ? { ...value, [lang]: content }
      : { [lang]: content };

    setValue(nextValue);
    onChange?.(JSON.stringify(nextValue));
  };

  return (
    <Field>
      <FieldSet>
        {availableLocales.map((lang, index) => (
          <div key={index} className={lang !== locale ? "hidden" : ""}>
            <TextEditor
              name={`${name}[${lang}]`}
              label={`${label} - ${lang.toUpperCase()}`}
              required={required}
              defaultValue={
                defaultValue
                  ? getNestedPropertyValue(defaultValue, lang)
                  : undefined
              }
              onChange={(content) => handleChange(content, lang)}
              {...props}
            />
          </div>
        ))}
        {error && (
          <FieldError className={"text-destructive text-sm"}>
            {error}
          </FieldError>
        )}
      </FieldSet>
    </Field>
  );
};

export default TranslatableTextEditor;
