import React, { useState } from "react";
import Navbar from "@/components/ui/navbar";
import { Sidebar } from "@/components/ui/Sidebar";
import { toast, ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import { usePage } from "@inertiajs/react";

const Layout = ({ children }: { children?: React.ReactNode }) => {
  const theme = window.localStorage.getItem("theme_mode") ?? "light";
  const {
    flash,
    props: { currentLocale },
  } = usePage();
  const [isOpen, setIsOpen] = useState(true);
  const toggleSidebar = () => {
    setIsOpen((prev) => !prev);
  };

  if (flash.success) {
    toast.success(flash.success);
  }

  if (flash.error) {
    toast.error(flash.error);
  }

  return (
    <>
      <div className={`flex max-h-screen overflow-y-scroll`}>
        <ToastContainer theme={theme} rtl={currentLocale == "ar"} />
        <div
          className={`bg-white-secondary dark:bg-dark-secondary h-screen shadow-lg ${
            isOpen ? "slide-sidebar-right" : "slide-sidebar-left w-1/4"
          }`}
        >
          <Sidebar isOpen={isOpen} toggleSidebar={toggleSidebar} />
        </div>
        <div
          className={`dark:bg-dark h-screen w-full overflow-y-scroll bg-white`}
        >
          <Navbar isSidebarOpen={isOpen} toggleSidebar={toggleSidebar} />
          <main className={"dark:bg-dark m-5 bg-white"}>{children}</main>
        </div>
      </div>
    </>
  );
};

export default Layout;
