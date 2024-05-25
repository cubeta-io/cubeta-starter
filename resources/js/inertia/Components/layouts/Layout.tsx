import React, { useState } from "react";
import Navbar from "@/Components/ui/Navbar";
import "../../../css/app.css";
import { Sidebar } from "@/Components/ui/Sidebar";
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

const Layout = ({ children }: { children?: React.ReactNode }) => {
  const [isOpen, setIsOpen] = useState(true);
  const toggleSidebar = () => {
    setIsOpen((prev) => !prev);
  };

  return (
    <>
      <div className={`flex max-h-screen overflow-y-scroll`}>
        <ToastContainer />
        <div
          className={`bg-white h-screen ${
            isOpen ? "slide-sidebar-right" : "slide-sidebar-left w-1/3"
          }`}
        >
          <Sidebar toggleSidebar={toggleSidebar} />
        </div>
        <div className={`w-full h-screen overflow-y-scroll`}>
          <Navbar isSidebarOpen={isOpen} toggleSidebar={toggleSidebar} />
          <main className={"m-5"}>{children}</main>
        </div>
      </div>
    </>
  );
};

export default Layout;
