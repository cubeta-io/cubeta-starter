import { Input as ShadcnInput } from "@/components/ui/input";
import { cn } from "@/lib/utils";
import { usePage } from "@inertiajs/react";
import { Eye, EyeOff } from "lucide-react";
import React, { useState } from "react";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";

export interface InputProps extends React.ComponentProps<"input"> {
  name: string;
  label?: string;
}

const Input: React.FC<InputProps> = ({
  name,
  label,
  type,
  defaultValue,
  className,
  placeholder = "",
  required = false,
  ...props
}) => {
  const {
    props: { errors },
  } = usePage();
  const isPasswordField = type === "password";
  const [showPassword, setShowPassword] = useState(false);

  return (
    <Field>
      {label && (
        <FieldLabel htmlFor={`${name}_id`}>
          {label}
          {required && <span className={"text-destructive"}>*</span>}
        </FieldLabel>
      )}
      <div className="relative">
        <ShadcnInput
          type={isPasswordField && showPassword ? "text" : (type ?? "text")}
          defaultValue={defaultValue}
          className={cn(className)}
          placeholder={placeholder}
          required={required}
          name={name}
          onWheel={
            type === "number" ? (e) => e.currentTarget.blur() : undefined
          }
          {...props}
        />
        {isPasswordField && (
          <button
            type="button"
            onClick={() => setShowPassword((current) => !current)}
            className="text-muted-foreground hover:text-foreground absolute inset-y-0 right-0 flex cursor-pointer items-center px-3"
            aria-label={showPassword ? "Hide password" : "Show password"}
            aria-pressed={showPassword}
          >
            {showPassword ? (
              <EyeOff className="size-4" />
            ) : (
              <Eye className="size-4" />
            )}
          </button>
        )}
      </div>
      {errors[name] && <FieldError>{errors[name]}</FieldError>}
    </Field>
  );
};

export default Input;
