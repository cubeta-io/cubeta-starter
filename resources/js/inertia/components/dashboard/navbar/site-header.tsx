import { Separator } from "@/components/ui/separator";
import { SidebarTrigger } from "@/components/ui/sidebar";
import { ThemeToggle } from "@/components/dashboard/navbar/theme-toggle";
import LanguageDropdown from "@/components/dashboard/navbar/language-dropdown";

export function SiteHeader() {
  return (
    <header className="flex h-(--header-height) shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear">
      <div className="flex w-full items-center gap-1 px-4 lg:gap-2 lg:px-6">
        <SidebarTrigger className="-ms-1" />
        <Separator
          orientation="vertical"
          className="mx-2 h-4 data-vertical:self-auto"
        />
        <h1 className="text-base font-medium">Documents</h1>
      </div>
      <div className={"flex flex-row items-center gap-2 px-5"}>
        <ThemeToggle />
        <LanguageDropdown />
      </div>
    </header>
  );
}
