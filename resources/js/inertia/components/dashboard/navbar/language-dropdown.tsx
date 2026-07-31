import React, { useState } from "react";
import Http from "@/modules/http/http";
import { usePage } from "@inertiajs/react";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Button } from "@/components/ui/button";
import { Loader } from "lucide-react";

const LanguageDropdown = () => {
  const {
    props: { currentLocale, availableLocales },
  } = usePage();

  const [loading, setLoading] = useState(false);

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
  };

  return (
    <DropdownMenu>
      <DropdownMenuTrigger
        render={
          <Button variant="outline" size="icon" className={"uppercase"}>
            {loading ? <Loader className={"animate-spin"} /> : currentLocale}
          </Button>
        }
      ></DropdownMenuTrigger>
      <DropdownMenuContent align="end">
        {availableLocales.map((l) => (
          <DropdownMenuItem
            className={"uppercase"}
            onClick={() => handleLocalChange(l)}
          >
            {l}
          </DropdownMenuItem>
        ))}
      </DropdownMenuContent>
    </DropdownMenu>
  );
};

export default LanguageDropdown;
