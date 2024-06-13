import React, { useEffect, useState } from "react";
import Sun from "@/Components/icons/Sun";
import Moon from "@/Components/icons/Moon";

const DarkModeToggle = () => {
    const currentTheme = window.localStorage.getItem("theme_mode") ?? "light";
    const [dark, setDark] = useState(currentTheme == "dark");
    const htmlTag = document.querySelector("html");

    useEffect(() => {
        if (dark) {
            htmlTag.classList.add("dark");
            htmlTag.classList.remove("light");
            window.localStorage.setItem("theme_mode", "dark");
        } else {
            htmlTag.classList.add("light");
            htmlTag.classList.remove("dark");
            window.localStorage.setItem("theme_mode", "light");
        }
    }, [dark]);

    return (
        <div
            id={"theme-color-mode-toggle"}
            onClick={() => {
                setDark((prevState) => !prevState);
            }}
            className={"p-4 cursor-pointer"}
        >
            {dark ? (
                <Sun className={"h-6 w-6 dark:text-white dark:fill-white"} />
            ) : (
                <Moon className={"h-6 w-6 dark:text-white dark:fill-white"} />
            )}
        </div>
    );
};

export default DarkModeToggle;
