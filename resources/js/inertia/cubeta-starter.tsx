import { createInertiaApp } from "@inertiajs/react";
import "../css/cubeta-starter.css";
import Layout from "@/components/layouts/layout";

createInertiaApp({
  layout: (name) => Layout,
  defaults: {
    visitOptions: () => {
      return { viewTransition: true };
    },
  },
});
