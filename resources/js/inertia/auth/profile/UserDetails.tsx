import Form from "@/Components/form/Form";
import Input from "@/Components/form/fields/Input";
import PageCard from "@/Components/ui/PageCard";
import { User } from "@/Models/User";
import { useForm } from "@inertiajs/react";
import { FormEvent, useState } from "react";

const UserDetails = ({ user }: { user: User }) => {
  const [selectedTab, setSelectedTab] = useState("overview");
  return (
    <PageCard>
      <div className="flex items-center gap-1">
        <div
          onClick={() => {
            setSelectedTab("overview");
          }}
          className={`hover:text-primary hover:border-b-primary cursor-pointer px-8 py-2 hover:border-b ${
            selectedTab == "overview"
              ? "text-primary border-b-primary border-b"
              : "dark:text-white"
          }`}
        >
          Overview
        </div>
        <div
          onClick={() => {
            setSelectedTab("edit_profile");
          }}
          className={`hover:text-primary hover:border-b-primary cursor-pointer px-8 py-2 hover:border-b ${
            selectedTab == "edit_profile"
              ? "text-primary border-b-primary border-b"
              : "dark:text-white"
          }`}
        >
          Edit Profile
        </div>
      </div>

      {selectedTab == "overview" && <UserOverview user={user} />}
      {selectedTab == "edit_profile" && <EditProfile user={user} />}
    </PageCard>
  );
};

export default UserDetails;

const UserOverview = ({ user }: { user: User }) => {
  return (
    <div className="w-full p-8">
      <h2 className="text-xl font-semibold dark:text-white">
        Profile Details :
      </h2>
      <div className="my-5 grid grid-cols-1 gap-3 md:grid-cols-2">
        <label className="flex items-center justify-between dark:text-white">
          <strong>User Name :</strong>
          <p>
            {user.first_name} {user.last_name}
          </p>
        </label>

        <label className="flex items-center justify-between dark:text-white">
          <strong>Email :</strong>
          <p>{user.email}</p>
        </label>
      </div>
    </div>
  );
};

const EditProfile = ({ user }: { user: User }) => {
  const { put, setData, processing } = useForm<{
    first_name?: string;
    last_name?: string;
    email?: string;
    password?: string;
    password_confirmation?: string;
    _method: "POST" | "PUT";
  }>();

  const onSubmit = (e: FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    put(route("web.protected.update-user-data"));
  };

  return (
    <div className="w-full p-8">
      <h2 className="text-xl font-semibold dark:text-white">Edit Profile :</h2>
      <Form onSubmit={onSubmit} processing={processing}>
        <div className="my-5 grid grid-cols-1 gap-3 md:grid-cols-2">
          <Input
            name="first_name"
            type="text"
            label="First Name"
            onChange={(e) => {
              setData("first_name", e.target.value);
            }}
            defaultValue={user?.first_name}
          />

          <Input
            name="last_name"
            type="text"
            label="Last Name"
            onChange={(e) => {
              setData("last_name", e.target.value);
            }}
            defaultValue={user?.last_name}
          />

          <Input
            name="email"
            type="email"
            label="Email"
            onChange={(e) => {
              setData("email", e.target.value);
            }}
            defaultValue={user?.email}
          />

          <Input
            name="password"
            label="New Password"
            type="password"
            onChange={(e) => {
              setData(
                "password",
                e.target.value.length <= 0 ? undefined : e.target.value,
              );
            }}
          />

          <Input
            name="password_confirmation"
            label="Password Confirmation"
            type="password"
            onChange={(e) => {
              setData(
                "password_confirmation",
                e.target.value.length <= 0 ? undefined : e.target.value,
              );
            }}
          />
        </div>
      </Form>
    </div>
  );
};
