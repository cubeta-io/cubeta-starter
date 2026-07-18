import { createInertiaApp } from "@inertiajs/react";
import "../css/cubeta-starter.css";
import Layout from "@/components/layouts/layout";

const authPages = [
  "login",
  "forget-password",
  "reset-password-code-form",
  "reset-password",
  "register",
];

createInertiaApp({
  layout: (name) => (authPages.includes(name) ? null : Layout),
  defaults: {
    visitOptions: () => {
      return { viewTransition: true };
    },
  },
});
