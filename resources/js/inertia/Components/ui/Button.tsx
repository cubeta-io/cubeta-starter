import React from "react";
import LoadingSpinner from "@/Components/icons/LoadingSpinner";

interface IButtonProps extends React.ComponentProps<"button"> {
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
        `disabled:hover:bg-opacity-15 flex cursor-pointer items-center disabled:cursor-not-allowed bg-${color} border hover:bg-white hover:dark:bg-transparent border-${color} hover:border-${color} text-white hover:text-${color} rounded-md p-2`
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
