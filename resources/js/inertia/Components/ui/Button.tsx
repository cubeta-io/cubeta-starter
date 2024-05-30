import React, { ButtonHTMLAttributes, useState } from "react";
import LoadingSpinner from "../icons/LoadingSpinner";

interface IButtonProps
    extends React.DetailedHTMLProps<
        ButtonHTMLAttributes<HTMLButtonElement>,
        HTMLButtonElement
    > {
    color?:
        | "brand"
        | "primary"
        | "secondary"
        | "success"
        | "info"
        | "warning"
        | "danger"
        | "light"
        | "dark";
}

const Button: React.FunctionComponent<IButtonProps> = ({
    className,
    children,
    disabled,
    color = "primary",
    ...props
}) => {
    const [colorClass , setColorClass] = useState(`bg-${color} hover:text-${color} hover:border-${color}`);
    return (
        <button
            className={
                className ??
                `${colorClass} p-2 disabled:bg-gray-400  disabled:cursor-not-allowed  disabled:hover:bg-gray-300  disabled:hover:border-gray-300  disabled:hover:text-white border  text-white rounded-md   hover:bg-white    flex items-center  justify-between`
            }
            disabled={disabled}
            {...props}
        >
            {children}
            {disabled ? <LoadingSpinner /> : ""}
        </button>
    );
};

export default Button;
