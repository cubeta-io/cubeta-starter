import ClosedEye from "@/components/icons/ClosedEye";
import Eye from "@/components/icons/Eye";
import { usePage } from "@inertiajs/react";
import React, { useState } from "react";
import Email from "@/components/icons/Email";

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
  const [show, setShow] = useState(false);

  const {
    props: { errors },
  } = usePage();

  return (
    <div className={`flex w-full flex-col p-0`}>
      <label
        htmlFor={`${name}-id`}
        className={
          type == "file"
            ? "flex flex-col items-start justify-between text-sm font-medium text-gray-900 dark:text-white"
            : `relative block rounded-md border border-gray-200 shadow-sm focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600`
        }
      >
        {type == "file" ? (label ?? "") : ""}
        <input
          type={type === "password" ? (show ? "text" : "password") : type}
          id={`${name}-id`}
          className={
            className ??
            (type == "file"
              ? "block h-full w-full cursor-pointer rounded-sm border border-gray-300 bg-transparent px-1 py-2 text-sm text-gray-900 shadow-sm focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400 dark:placeholder-gray-400"
              : "peer w-full border-none bg-transparent placeholder-transparent focus:border-transparent focus:ring-0 focus:outline-none dark:text-white")
          }
          placeholder={placeholder}
          name={name}
          required={required ?? false}
          defaultValue={defaultValue}
          {...props}
        />

        {type === "email" && (
          <span className={"absolute top-2 right-2"}>
            <Email />
          </span>
        )}

        {type === "password" ? (
          show ? (
            <button
              className={"absolute top-2 right-2"}
              type={"button"}
              onClick={() => setShow((prevState) => !prevState)}
            >
              <Eye />
            </button>
          ) : (
            <button
              className={"absolute top-2 right-2"}
              onClick={() => setShow((prevState) => !prevState)}
              type={"button"}
            >
              <ClosedEye />
            </button>
          )
        ) : (
          ""
        )}

        <span className="bg-white-secondary dark:bg-dark-secondary pointer-events-none absolute inset-s-2.5 top-0 -translate-y-1/2 p-0.5 text-xs text-gray-700 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs dark:text-white">
          {type == "file" ? "" : label}
          {type != "file" && required ? (
            <span className="text-sm text-red-500">*</span>
          ) : (
            ""
          )}
        </span>
      </label>
      {errors[name] ? (
        <p className={"text-sm text-red-500"}>{errors[name]}</p>
      ) : (
        ""
      )}
    </div>
  );
};

export default Input;
