import { usePage } from "@inertiajs/react";
import { ReactNode, createContext, useState } from "react";

export const LocaleContext = createContext<string>("en");

const TranslatableInputsContext = ({ children }: { children: ReactNode }) => {
    const [locale, setLocale] = useState(
        usePage().props.currentLocale as string
    );

    const availableLocales = usePage().props.availableLocales as string[];

    return (
        <LocaleContext.Provider value={locale}>
            <div className="flex justify-end items-center my-4 lang-btn-holder">
                {availableLocales.map((lang, index) => (
                    <label
                        className="flex justify-center items-center border-primary has-[:checked]:border-primar bg-white dark:bg-dark-secondary dark:text-white has-[:checked]:bg-primary px-3 py-2 border borderbg-primary rounded-md text-gray-900 has-[:checked]:text-white cursor-pointer lang-btn"
                        key={index}
                    >
                        <input
                            type="radio"
                            className="border-primary sr-only"
                            value={lang}
                            checked={lang == locale}
                            onChange={() => {
                                setLocale(lang);
                            }}
                        />
                        <p className="font-medium text-sm">
                            {lang.toUpperCase()}
                        </p>
                    </label>
                ))}
            </div>
            {children}
        </LocaleContext.Provider>
    );
};

export default TranslatableInputsContext;
