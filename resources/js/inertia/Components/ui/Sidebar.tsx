import React from "react";
import {asset} from "@/helper";
import Menu from "../icons/Menu";
import {Link} from "@inertiajs/react";

const sidebarItems = [
    {
        href: "/",
        title: "Dashboard",
    }
];

export const Sidebar = ({toggleSidebar}: { toggleSidebar: () => void }) => {
    return (
        <div
            className="sticky flex flex-col justify-between bg-white-secondary dark:bg-dark-secondary max-h-screen overflow-y-scroll">
            <div
                className={
                    "flex justify-between items-center sticky top-0 bg-white-secondary dark:bg-dark-secondary p-[17px] max-h-20 shadow-sm"
                }
            >
                <div className={`flex items-center gap-1`}>
                    <img
                        src={asset("/images/cubeta-logo.png")}
                        width={"17px"}
                        alt=""
                    />
                    <a
                        href="#"
                        className="px-2 w-full text-xl text-brand dark:text-white hover:underline"
                    >
                        Cubeta Starter
                    </a>
                </div>

                <button type={"button"} onClick={() => toggleSidebar()}>
                    <Menu className="w-8 h-8 text-brand dark:text-white"/>
                </button>
            </div>

            <div
                id="sidebar-list"
                className={"bg-white-secondary dark:bg-dark-secondary w-full mt-6 gap-1 px-4"}
            >
                {sidebarItems.map((item, index) => (
                    <SidebarItem
                        key={index}
                        href={item.href}
                        title={item.title}
                    />
                ))}
            </div>
        </div>
    );
};

export const SidebarItem = ({
                                href,
                                title,
                            }: {
    href: string;
    title: string;
}) => {
    const selected = window.location.href === href;

    return (
        <div className="mb-3">
            <Link
                className={`flex text-lg font-semibold gap-5 items-center px-4 py-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 rounded-lg ${
                    selected ? "bg-sky-100 dark:bg-white-secondary dark:text-black" : " dark:text-white"
                }`}
                href={href}
            >
                <span>{title}</span>
            </Link>
        </div>
    );
};

export const CompactSidebarItem = ({
                                       title,
                                       children,
                                   }: {
    title: string;
    children?: React.ReactNode;
}) => {
    return (
        <details className="[&_summary::-webkit-details-marker]:hidden group">
            <summary
                className="flex text-lg font-semibold justify-between items-center hover:bg-gray-100 px-4 py-2 rounded-lg text-gray-500 hover:text-gray-700 cursor-pointer">
                <span> {title} </span>

                <span className="group-open:-rotate-180 transition duration-300 shrink-0">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="w-5 h-5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fillRule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clipRule="evenodd"
                            />
                        </svg>
                    </span>
            </summary>
            <ul className="space-y-1 mt-2 px-4">{children}</ul>
        </details>
    );
};
