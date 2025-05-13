import Layout from "@/Components/layouts/Layout";
import { createInertiaApp } from "@inertiajs/react";
import { Suspense } from "react";
import { createRoot } from "react-dom/client";
import "../css/cubeta-starter.css";
import "./bootstrap";

const appName = import.meta.env.APP_NAME || "Laravel";

const authPages = [
  "Login",
  "ForgetPassword",
  "ResetPasswordCodeForm",
  "ResetPassword",
  "Register",
];

createInertiaApp({
  title: (title: any) => `${title} - ${appName}`,
  resolve: async (name: any) => {
    // @ts-ignore
    const pages = import.meta.glob("./Pages/**/*.tsx");
    // @ts-ignore
    let page = (await pages[`./Pages/${name}.tsx`]()).default;

    // Assign layout conditionally
    page.layout =
      page.layout ||
      (!authPages.includes(page.name ?? "undefined")
        ? // @ts-ignore
        (page) => <Layout children={page} />
        : null);

    return page;
  },
  setup({ el, App, props }) {
    const root = createRoot(el);

    root.render(
      <Suspense fallback={<div>Loading...</div>}>
        <App {...props} />
      </Suspense>,
    );
  },
  progress: {
    color: "#4B5563",
  },
});

const dark =
  "dark" == window.localStorage.getItem("theme_mode") ? "dark" : "light";
const htmlTag = document.querySelector("html");
if (dark) {
  htmlTag?.classList.add("dark");
  htmlTag?.classList.remove("light");
} else {
  htmlTag?.classList.add("light");
  htmlTag?.classList.remove("dark");
}
