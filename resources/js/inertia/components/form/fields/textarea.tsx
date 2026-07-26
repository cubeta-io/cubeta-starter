import { Textarea as ShadcnTextarea } from "@/components/ui/textarea";
import { usePage } from "@inertiajs/react";
import React from "react";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";

export interface TextareaProps extends React.ComponentProps<"textarea"> {
  name: string;
  label?: string;
}

const Textarea: React.FC<TextareaProps> = ({
  name,
  label,
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
      <ShadcnTextarea id={`${name}_id`} rows={4} name={name ?? ""} {...props} />
      {error && <FieldError>{error}</FieldError>}
    </Field>
  );
};

export default Textarea;
