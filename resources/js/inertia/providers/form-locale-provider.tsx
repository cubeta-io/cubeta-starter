import { usePage } from "@inertiajs/react";
import {
  createContext,
  Dispatch,
  ReactNode,
  SetStateAction,
  useContext,
  useState,
} from "react";
import { AvailableLocales } from "@/models/translatable";

const LocaleContext = createContext<{
  locale: AvailableLocales;
  setLocale: Dispatch<SetStateAction<AvailableLocales>>;
} | null>(null);

const FormLocaleProvider = ({
  withLanguageRadio = true,
  children,
}: {
  withLanguageRadio?: boolean;
  children: ReactNode;
}) => {
  const {
    props: { availableLocales, currentLocale },
  } = usePage();
  const [locale, setLocale] = useState(currentLocale);

  return (
    <LocaleContext.Provider value={{ locale, setLocale }}>
      {withLanguageRadio && (
        <div className="lang-btn-holder my-4 flex items-center justify-end">
          {availableLocales.map((lang, index) => (
            <label
              className="border-primary has-checked:border-primar dark:bg-dark-secondary has-checked:bg-primary borderbg-primary lang-btn flex cursor-pointer items-center justify-center rounded-md border bg-white px-3 py-2 text-gray-900 has-[:checked]:text-white dark:text-white"
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
      )}
      {children}
    </LocaleContext.Provider>
  );
};

export function useFormLocale() {
  const context = useContext(LocaleContext);

  return { ...context };
}

export default FormLocaleProvider;
