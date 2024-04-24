import { useState } from "react";
import { usePage } from "@inertiajs/react";

const LanguageDropdown = () => {
    const { currentLocale, availableLocales } = usePage().props;
    const [open, setOpen] = useState(false);
    const [selectedLocale, setSelectedLocale] = useState(currentLocale);

    return (
        <div className="relative w-auto">
            <div
                className="inline-flex items-center bg-white border rounded-md overflow-hidden"
                onClick={() => setOpen((prevState) => !prevState)}
            >
                <a
                    href="#"
                    className="flex bg-primary p-2 text-sm/none text-white"
                >
                    {selectedLocale as string}
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="w-4 h-4"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fillRule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clipRule="evenodd"
                        />
                    </svg>
                </a>
            </div>

            <div
                className={`${open ? "absolute" : "hidden"} end-0 z-10 mt-2 w-20 rounded-md border border-gray-100 bg-white shadow-lg`}
                role="menu"
            >
                <div className="p-2">
                    {(availableLocales as string[]).map((locale) => (
                        <span
                            className="block hover:bg-gray-50 px-4 py-2 rounded-lg text-gray-500 text-sm hover:text-gray-700 cursor-pointer"
                            role="menuitem"
                            key={locale}
                            onClick={() => setSelectedLocale(locale)}
                        >
                            {locale}
                        </span>
                    ))}
                </div>
            </div>
        </div>
    );
};

export default LanguageDropdown;
