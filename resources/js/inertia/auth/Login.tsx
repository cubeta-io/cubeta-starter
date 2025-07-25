import Form from "@/Components/form/Form";
import Input from "@/Components/form/fields/Input";
import PageCard from "@/Components/ui/PageCard";
import { asset } from "@/helper";
import { Link, useForm } from "@inertiajs/react";
import { FormEvent } from "react";

const Login = () => {
  const { post, setData, errors, processing } = useForm<{
    email: string;
    password: string;
  }>();

  const onSubmit = (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    post(route("web.public.login"));
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
                  Welcome Back
                </h1>
              </div>
              <div className="flex items-center justify-center dark:text-white">
                <p>Please Login To Your Account</p>
              </div>
            </div>
            <Form
              onSubmit={onSubmit}
              processing={processing}
              buttonText="Login"
            >
              <div className="my-5 flex w-full flex-col gap-5">
                <Input
                  name="email"
                  onChange={(e) => setData("email", e.target.value)}
                  label="Email"
                  required={true}
                  type="email"
                />

                <Input
                  name="password"
                  onChange={(e) => setData("password", e.target.value)}
                  label="Password"
                  required={true}
                  type="password"
                />
              </div>
              <p className="text-lg dark:text-white">
                Forgot Your Password ?{" "}
                <span>
                  <Link
                    href={route("web.public.request-reset-password-code-page")}
                    className="hover:text-primary text-blue-700"
                  >
                    Reset Your Password
                  </Link>
                </span>
              </p>
              <p className="text-lg dark:text-white">
                New User ?{" "}
                <span>
                  <Link
                    href={route("web.public.register-page")}
                    className="hover:text-primary text-blue-700"
                  >
                    Create New Account Now
                  </Link>
                </span>
              </p>
            </Form>
          </PageCard>
        </div>
      </div>
    </div>
  );
};

export default Login;
