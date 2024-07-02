import { FormEvent, ReactNode } from "react";
import Button from "../ui/Button";
import ChevronLeft from "../icons/ChevronLeft";

const Form = ({
                  onSubmit,
                  processing,
                  children,
                  buttonText = "Save",
              }: {
    onSubmit: (e: FormEvent<HTMLFormElement>) => void;
    processing?: boolean;
    children?: ReactNode;
    buttonText?: string;
}) => {
    return (
        <form onSubmit={onSubmit}>
            {children}
            <div className={"flex items-center justify-between w-full my-2"}>
                <Button
                    type="button"
                    color="secondary"
                    onClick={(e) => {
                        e.preventDefault();
                        window.history.back();
                    }}
                >
                    <ChevronLeft />
                    Back
                </Button>
                <Button type="submit" disabled={processing}>
                    {buttonText}
                </Button>
            </div>
        </form>
    );
};

export default Form;
