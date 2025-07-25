import Form from "@/Components/form/Form";
import Input from "@/Components/form/fields/Input";
import PageCard from "@/Components/ui/PageCard";
import { asset } from "@/helper";
import { Link, useForm } from "@inertiajs/react";
import { FormEvent } from "react";

const Register = () => {
  const { post, setData, errors, processing } = useForm<{
    first_name: string;
    last_name: string;
    email: string;
    password: string;
    password_confirmation: string;
  }>();

  const onSubmit = (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    post(route("web.public.register"));
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
                  Hello There !
                </h1>
              </div>
              <div className="flex items-center justify-center dark:text-white">
                <p>Fill The Information Below To Continue</p>
              </div>
            </div>
            <Form
              onSubmit={onSubmit}
              processing={processing}
              buttonText="Sign Up"
            >
              <div className="my-5 flex w-full flex-col gap-5">
                <div className="grid grid-cols-1 gap-1 md:grid-cols-2">
                  <Input
                    name="first_name"
                    onChange={(e) => setData("first_name", e.target.value)}
                    label="First Name"
                    required={true}
                    type="text"
                  />
                  <Input
                    name="last_name"
                    onChange={(e) => setData("last_name", e.target.value)}
                    label="Last Name"
                    required={true}
                    type="text"
                  />
                </div>
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

                <Input
                  name="password_confirmation"
                  onChange={(e) =>
                    setData("password_confirmation", e.target.value)
                  }
                  label="Confirm Password"
                  required={true}
                  type="password"
                />
              </div>
              <p className="text-lg dark:text-white">
                Have An Account ?{" "}
                <span>
                  <Link
                    href={route("web.public.login.page")}
                    className="hover:text-primary text-blue-700"
                  >
                    Login
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

export default Register;
