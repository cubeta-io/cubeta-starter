import Form from "@/Components/form/Form";
import Input from "@/Components/form/fields/Input";
import PageCard from "@/Components/ui/PageCard";
import { asset } from "@/helper";
import { useForm } from "@inertiajs/react";
import { FormEvent } from "react";

const ResetPasswordCodeForm = () => {
  const { post, setData, errors, processing } = useForm<{
    reset_password_code: string;
  }>();

  const onSubmit = (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    post(route("web.public.validate-reset-password-code"));
  };
  return (
    <div className="my-20 grid grid-cols-3">
      <div className="col-start-2 col-end-3">
        <div className="flex flex-col items-center">
          <div className="my-2 flex items-center gap-1">
            <img src={asset("images/cubeta-logo.png")} width={"35px"} />
            <h1 className="text-brand text-4xl font-bold">Cubeta Starter</h1>
          </div>
          <PageCard>
            <div className="my-5 flex flex-col">
              <div className="flex items-center justify-center">
                <h1 className="text-brand text-center text-2xl font-semibold">
                  Please Check Your Email For An Email From Us !
                </h1>
              </div>
              <div className="flex items-center justify-center">
                <p className={"dark:text-white"}>
                  Enter the reset code sent within the email below
                </p>
              </div>
            </div>
            <Form onSubmit={onSubmit} processing={processing}>
              <Input
                label="Password Reset Code"
                name={"reset_password_code"}
                required={true}
                onChange={(e) => {
                  setData("reset_password_code", e.target.value);
                }}
                type="text"
              />
            </Form>
          </PageCard>
        </div>
      </div>
    </div>
  );
};

export default ResetPasswordCodeForm;
