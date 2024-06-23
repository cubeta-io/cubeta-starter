import { FormEvent, ReactNode } from "react";
import Button from "../ui/Button";

const Form = ({
                  onSubmit,
                  processing,
                  children,
                  buttonText = "Submit",
              }: {
    onSubmit: (e: FormEvent<HTMLFormElement>) => void;
    processing?: boolean;
    children?: ReactNode;
    buttonText?: string;
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
