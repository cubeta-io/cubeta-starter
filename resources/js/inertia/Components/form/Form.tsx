import { FormEvent, ReactNode } from "react";
import Button from "../ui/Button";

const Form = ({
  onSubmit,
  processing,
  children,
}: {
  onSubmit: (e: FormEvent<HTMLFormElement>) => void;
  processing?: boolean;
  children?: ReactNode;
}) => {
  return (
    <form onSubmit={onSubmit}>
      {children}
      <div className={"flex items-center justify-center my-2"}>
        <Button type="submit" disabled={processing}>
          Submit
        </Button>
      </div>
    </form>
  );
};

export default Form;
