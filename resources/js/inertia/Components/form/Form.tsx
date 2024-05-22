import { FormEvent, ReactNode } from "react";
import Button from "../ui/Button";

const Form = ({
                  onSubmit,
                  processing,
                  children,
                  buttonText = "submit",
              }: {
    onSubmit: (e: FormEvent<HTMLFormElement>) => void;
    buttonText?: string;
    processing?: boolean;
    children?: ReactNode;
}) => {
    return (
        <form onSubmit={onSubmit}>
            {children}
            <div className={"flex items-center justify-center my-2"}>
                <Button type="submit" disabled={processing}>
                    {buttonText}
                </Button>
            </div>
        </form>
    );
};

export default Form;
