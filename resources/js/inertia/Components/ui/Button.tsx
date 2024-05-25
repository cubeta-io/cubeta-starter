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
        `p-2 disabled:bg-gray-400  disabled:cursor-not-allowed  disabled:hover:bg-gray-300  disabled:hover:border-gray-300  disabled:hover:text-white border  text-white rounded-md bg-${color}  hover:bg-white hover:text-${color}  hover:border-${color}  flex items-center  justify-between`
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
