import { usePage } from "@inertiajs/react";
import { createContext, ReactNode, useState } from "react";

export const LocaleContext = createContext<string>("en");

const TranslatableInputsContext = ({ children }: { children: ReactNode }) => {
  const [locale, setLocale] = useState(usePage().props.currentLocale as string);

  const availableLocales = usePage().props.availableLocales as string[];

  return (
    <LocaleContext.Provider value={locale}>
      <div className="lang-btn-holder my-4 flex items-center justify-end">
        {availableLocales.map((lang, index) => (
          <label
            className="border-primary has-[:checked]:border-primar dark:bg-dark-secondary has-[:checked]:bg-primary borderbg-primary lang-btn flex cursor-pointer items-center justify-center rounded-md border bg-white px-3 py-2 text-gray-900 has-[:checked]:text-white dark:text-white"
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
            <p className="text-sm font-medium">{lang.toUpperCase()}</p>
          </label>
        ))}
      </div>
      {children}
    </LocaleContext.Provider>
  );
};

export default TranslatableInputsContext;
