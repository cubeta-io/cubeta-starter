import Form from "@/Components/form/Form";
import Input from "@/Components/form/fields/Input";
import PageCard from "@/Components/ui/PageCard";
import { asset } from "@/helper";
import { useForm } from "@inertiajs/react";
import { FormEvent } from "react";

const ForgetPassword = () => {
  const { post, setData, errors, processing } = useForm<{
    email: string;
  }>();

  const onSubmit = (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    post(route("web.public.request-reset-password-code"));
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
                <h1 className="text-brand text-3xl font-semibold">
                  Forget Your Password ?
                </h1>
              </div>
              <div className="flex items-center justify-center">
                <p className="mt-1 text-center dark:text-white">
                  Enter Your Email So We Can Send You A Reset Password Code
                </p>
              </div>
            </div>
            <Form onSubmit={onSubmit} processing={processing}>
              <Input
                name={"email"}
                onChange={(e) => setData("email", e.target.value)}
                label="Email"
                required={true}
                type="email"
              />
            </Form>
          </PageCard>
        </div>
      </div>
    </div>
  );
};

export default ForgetPassword;
