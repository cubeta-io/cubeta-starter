import ClosedEye from "@/Components/icons/ClosedEye";
import Eye from "@/Components/icons/Eye";
import {usePage} from "@inertiajs/react";
import React, {ChangeEvent, HTMLProps, useState} from "react";

export interface InputProps extends HTMLProps<HTMLInputElement> {
    name: string;
    label?: string;
    type?: string;
    placeholder?: string;
    defaultValue?: any;
    className?: string;
    onInput?: (e: ChangeEvent<HTMLInputElement>) => void;
    onChange?: (e: ChangeEvent<HTMLInputElement>) => void;
    required?: boolean;
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

    const errors = usePage().props.errors;

    return (
        <div className={`flex flex-col w-full`}>
            <label
                htmlFor={`${name}-id`}
                className={
                    type == "file"
                        ? "block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                        : `block relative border-gray-200 shadow-sm border focus-within:border-blue-600 rounded-md focus-within:ring-1 focus-within:ring-blue-600`
                }
            >
                {type == "file" ? label ?? "" : ""}
                <input
                    type={
                        type === "password"
                            ? show
                                ? "text"
                                : "password"
                            : type
                    }
                    id={`${name}-id`}
                    className={
                        className ??
                        (type == "file"
                            ? "block w-full text-sm text-gray-900 border border-gray-300 p-1 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                            : "peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0 w-full")
                    }
                    placeholder={placeholder}
                    name={name}
                    required={required ?? false}
                    {...props}
                />

                {type === "email" && (
                    <span className={"absolute right-2 top-2"}>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            strokeWidth={1.5}
                            stroke="currentColor"
                            className="w-6 h-6"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                d="M16.5 12a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Zm0 0c0 1.657 1.007 3 2.25 3S21 13.657 21 12a9 9 0 1 0-2.636 6.364M16.5 12V8.25"
                            />
                        </svg>
                    </span>
                )}

                {type === "password" ? (
                    show ? (
                        <button
                            className={"absolute right-2 top-2"}
                            type={"button"}
                            onClick={() => setShow((prevState) => !prevState)}
                        >
                            <Eye/>
                        </button>
                    ) : (
                        <button
                            className={"absolute right-2 top-2"}
                            onClick={() => setShow((prevState) => !prevState)}
                            type={"button"}
                        >
                            <ClosedEye/>
                        </button>
                    )
                ) : (
                    ""
                )}

                <span
                    className="top-0 peer-focus:top-0 peer-placeholder-shown:top-1/2 absolute bg-white p-0.5 text-gray-700 text-xs peer-placeholder-shown:text-sm peer-focus:text-xs transition-all -translate-y-1/2 pointer-events-none start-2.5">
                    {type == "file" ? "" : label}
                    {type != "file" && required ? (
                        <span className="text-red-500 text-sm">*</span>
                    ) : (
                        ""
                    )}
                </span>
            </label>
            {errors[name] ? (
                <p className={"text-red-500 text-sm"}>{errors[name]}</p>
            ) : (
                ""
            )}
        </div>
    );
};

export default Input;
