import LoadingSpinner from "@/Components/icons/LoadingSpinner";
import { AvailableLocales } from "@/Models/Translatable";
import Http from "@/Modules/Http/Http";
import { MiddlewareProps } from "@/types";
import { usePage } from "@inertiajs/react";
import { useEffect, useRef, useState } from "react";

const LanguageDropdown = () => {
  const { currentLocale, availableLocales } = usePage<MiddlewareProps>().props;
  const [open, setOpen] = useState(false);
  const [selectedLocale, setSelectedLocale] = useState<
    string | AvailableLocales
  >(currentLocale as string);

  const [loading, setLoading] = useState(false);

  const selectorRef = useRef<HTMLDivElement>(null);

  const handleClickOutside = (event: MouseEvent) => {
    if (
      selectorRef.current &&
      !selectorRef.current.contains(event.target as Node)
    ) {
      setOpen(false);
    }
  };

  useEffect(() => {
    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
    };
  }, []);

  const handleLocalChange = (locale: string) => {
    setLoading(true);
    Http.make()
      .post(route("set-locale"), {
        lang: locale,
      })
      .then(() => {
        setLoading(false);
        window.localStorage.setItem("locale", locale);
        location.reload();
      });
    setSelectedLocale(locale as AvailableLocales);
    setOpen(false);
  };

  return (
    <div ref={selectorRef} className="relative w-auto cursor-pointer">
      {loading ? (
        <LoadingSpinner />
      ) : (
        <>
          <div
            className="bg-white-secondary dark:bg-dark-secondary inline-flex items-center overflow-hidden rounded-md"
            onClick={() => setOpen((prevState) => !prevState)}
          >
            <div className="bg-primary flex p-2 text-sm/none text-white">
              {selectedLocale as string}
              <svg
                xmlns="http://www.w3.org/2000/svg"
                className="h-4 w-4"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fillRule="evenodd"
                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                  clipRule="evenodd"
                />
              </svg>
            </div>
          </div>

          <div
            className={`${open ? "absolute" : "hidden"} bg-white-secondary dark:bg-dark-secondary end-0 z-10 mt-2 w-20 rounded-md shadow-lg`}
            role="menu"
          >
            <div className="p-2">
              {(availableLocales as string[]).map((locale) => (
                <span
                  className="block cursor-pointer rounded-lg px-4 py-2 text-sm hover:bg-gray-50 hover:text-gray-700 dark:text-white"
                  role="menuitem"
                  key={locale}
                  onClick={() => {
                    handleLocalChange(locale);
                  }}
                >
                  {locale}
                </span>
              ))}
            </div>
          </div>
        </>
      )}
    </div>
  );
};

export default LanguageDropdown;
