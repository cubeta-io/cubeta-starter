import React, {ButtonHTMLAttributes} from "react";
import LoadingSpinner from "@/Components/icons/LoadingSpinner";

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
                `flex items-center cursor-pointer disabled:hover:bg-opacity-15 disabled:cursor-not-allowed bg-${color} hover:bg-white hover:dark:bg-transparent border border-${color} hover:border-${color} text-white hover:text-${color} p-2 rounded-md`
            }
            disabled={disabled}
            {...props}
        >
            {children}
            {disabled ? <LoadingSpinner/> : ""}
        </button>
    );
};

export default Button;
