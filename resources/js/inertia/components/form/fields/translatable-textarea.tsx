import { getNestedPropertyValue } from "@/helper";
import { Translatable, translate } from "@/models/translatable";
import { usePage } from "@inertiajs/react";
import React, { useState } from "react";
import { useFormLocale } from "@/providers/form-locale-provider";
import { Field, FieldError, FieldSet } from "@/components/ui/field";
import Textarea from "@/components/form/fields/textarea";

interface TranslatableProps extends Omit<
  React.ComponentProps<"textarea">,
  "defaultValue" | "onChange" | "onInput"
> {
  defaultValue?: string | object | Translatable | undefined;
  label?: string;
  onChange?: (v: string) => void;
}

const TranslatableTextarea: React.FC<TranslatableProps> = ({
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
    e: React.ChangeEvent<HTMLTextAreaElement, HTMLTextAreaElement>,
    lang: string | keyof Translatable,
  ) => {
    const nextValue = value
      ? { ...value, [lang]: e.target.value }
      : { [lang]: e.target.value };

    setValue(nextValue);
    onChange?.(JSON.stringify(nextValue));
  };

  return (
    <Field>
      <FieldSet>
        {availableLocales.map((lang, index) => (
          <div key={index} className={lang !== locale ? "hidden" : ""}>
            <Textarea
              rows={4}
              name={`${name}[${lang}]`}
              label={`${label} - ${lang.toUpperCase()}`}
              defaultValue={
                defaultValue ? getNestedPropertyValue(defaultValue, lang) : ""
              }
              onChange={(e) => handleChange(e, lang)}
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

export default TranslatableTextarea;
