import { ReactNode, SubmitEventHandler } from "react";
import { Button } from "@/components/ui/button";
import { ChevronLeft, Loader } from "lucide-react";
import { FieldGroup } from "@/components/ui/field";
import { useTranslation } from "@/hooks/use-translation";

const Form = ({
  onSubmit,
  processing,
  children,
  buttonText,
  backButton = true,
}: {
  onSubmit: SubmitEventHandler<HTMLFormElement>;
  processing?: boolean;
  children?: ReactNode;
  buttonText?: string;
  backButton?: boolean;
}) => {
  const { t } = useTranslation();
  const submitText = buttonText ?? t("dashboard.actions.save");
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
              {t("dashboard.actions.back")}
            </Button>
          )}
          <Button type="submit" disabled={processing}>
            {submitText}
            {processing && <Loader className={"animate-spin"} />}
          </Button>
        </FieldGroup>
      </FieldGroup>
    </form>
  );
};

export default Form;
