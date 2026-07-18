import { createInertiaApp } from "@inertiajs/react";
import "../css/cubeta-starter.css";
import "./bootstrap";
import Layout from "@/components/layouts/Layout";

const authPages = [
  "Login",
  "ForgetPassword",
  "ResetPasswordCodeForm",
  "ResetPassword",
  "Register",
];

createInertiaApp({
  layout: (name) => (authPages.includes(name) ? null : Layout),
  defaults: {
    visitOptions: () => {
      return { viewTransition: true };
    },
  },
});
