import React, {useState} from "react";
import Navbar from "@/Components/ui/Navbar";
import {Sidebar} from "@/Components/ui/Sidebar";
import {toast, ToastContainer} from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import {usePage} from "@inertiajs/react";
import {MiddlewareProps} from "@/types";

const Layout = ({children}: { children?: React.ReactNode }) => {
    const theme = window.localStorage.getItem("theme_mode") ?? "light";
    const [isOpen, setIsOpen] = useState(true);
    const toggleSidebar = () => {
        setIsOpen((prev) => !prev);
    };

    if (usePage<MiddlewareProps>().props.message) {
        toast.info(usePage<MiddlewareProps>().props.message);
        usePage<MiddlewareProps>().props.message = undefined;
    }

    if (usePage<MiddlewareProps>().props.success) {
        toast.success(usePage<MiddlewareProps>().props.success);
        usePage<MiddlewareProps>().props.success = undefined;
    }

    if (usePage<MiddlewareProps>().props.error) {
        toast.error(usePage<MiddlewareProps>().props.error);
        usePage<MiddlewareProps>().props.success = undefined;
    }

    return (
        <>
            <div className={`flex max-h-screen overflow-y-scroll`}>
                <ToastContainer
                    theme={theme}
                    rtl={usePage<MiddlewareProps>().props.currentLocale == "ar"}
                />
                <div
                    className={`bg-white-secondary shadow-lg dark:bg-dark-secondary h-screen ${
                        isOpen
                            ? "slide-sidebar-right"
                            : "slide-sidebar-left w-1/4"
                    }`}
                >
                    <Sidebar isOpen={isOpen} toggleSidebar={toggleSidebar}/>
                </div>
                <div
                    className={`w-full h-screen overflow-y-scroll bg-white dark:bg-dark`}
                >
                    <Navbar
                        isSidebarOpen={isOpen}
                        toggleSidebar={toggleSidebar}
                    />
                    <main className={"m-5 bg-white dark:bg-dark"}>
                        {children}
                    </main>
                </div>
            </div>
        </>
    );
};

export default Layout;
