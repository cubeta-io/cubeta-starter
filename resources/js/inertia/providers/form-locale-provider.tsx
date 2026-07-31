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
import { ToggleGroup, ToggleGroupItem } from "@/components/ui/toggle-group";

const FormLocaleContext = createContext<{
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
    <FormLocaleContext.Provider value={{ locale, setLocale }}>
      {withLanguageRadio && (
        <div className="lang-btn-holder my-4 flex items-center justify-end">
          <ToggleGroup
            variant={"outline"}
            onValueChange={(v) => setLocale(v[0] as AvailableLocales)}
            multiple={false}
            itemType={"single"}
            value={[locale]}
          >
            {availableLocales.map((lang, index) => (
              <ToggleGroupItem key={index} value={lang}>
                {lang.toUpperCase()}
              </ToggleGroupItem>
            ))}
          </ToggleGroup>
        </div>
      )}
      {children}
    </FormLocaleContext.Provider>
  );
};

export function useFormLocale() {
  const context = useContext(FormLocaleContext);

  return { ...context };
}

export default FormLocaleProvider;