import React, { ButtonHTMLAttributes } from "react";
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
    return (
        <button
            className={
                className ??
                `bg-${color} hover:text-${color} border-${color} p-2 text-white rounded-md hover:dark:bg-transparent`
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
