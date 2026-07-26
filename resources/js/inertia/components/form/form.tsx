import { ReactNode, SubmitEventHandler } from "react";
import { Button } from "@/components/ui/button";
import { ChevronLeft, Loader } from "lucide-react";
import { FieldGroup } from "@/components/ui/field";

const Form = ({
  onSubmit,
  processing,
  children,
  buttonText = "Save",
  backButton = true,
}: {
  onSubmit: SubmitEventHandler<HTMLFormElement>;
  processing?: boolean;
  children?: ReactNode;
  buttonText?: string;
  backButton?: boolean;
}) => {
  return (
    <form onSubmit={onSubmit}>
      <FieldGroup>
        {children}
        <FieldGroup
          className={`flex flex-row items-center ${backButton ? "justify-between" : "justify-end"} w-full`}
        >
          {backButton && (
            <Button
              type="button"
              variant={"secondary"}
              onClick={(e) => {
                e.preventDefault();
                window.history.back();
              }}
            >
              <ChevronLeft />
              Back
            </Button>
          )}
          <Button type="submit" disabled={processing}>
            {buttonText}
            {processing && <Loader className={"animate-spin"} />}
          </Button>
        </FieldGroup>
      </FieldGroup>
    </form>
  );
};

export default Form;
