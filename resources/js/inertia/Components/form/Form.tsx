import {FormEvent, ReactNode} from "react";
import Button from "../ui/Button";
import ChevronLeft from "../icons/ChevronLeft";

const Form = ({
                  onSubmit,
                  processing,
                  children,
                  buttonText = "Save",
                  backButton = true,
              }: {
    onSubmit: (e: FormEvent<HTMLFormElement>) => void;
    processing?: boolean;
    children?: ReactNode;
    buttonText?: string;
    backButton?: boolean;
}) => {
    return (
        <form onSubmit={onSubmit}>
            {children}
            <div className={`flex items-center ${backButton ? "justify-between" : "justify-end"} w-full my-2`}>
                {backButton
                    ? (<Button
                        type="button"
                        color="secondary"
                        onClick={(e) => {
                            e.preventDefault();
                            window.history.back();
                        }}
                    >
                        <ChevronLeft/>
                        Back
                    </Button>)
                    : ""}
                <Button type="submit" disabled={processing}>
                    {buttonText}
                </Button>
            </div>
        </form>
    );
};

export default Form;
