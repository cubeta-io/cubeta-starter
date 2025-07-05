import LanguageDropdown from "@/Components/ui/LanguageDropDown";
import ProfileDropdown from "@/Components/ui/ProfileDropdown";
import Menu from "@/Components/icons/Menu";
import DarkModeToggle from "@/Components/ui/DarkModeToggle";

const Navbar = ({
  isSidebarOpen,
  toggleSidebar,
}: {
  isSidebarOpen: boolean;
  toggleSidebar: () => void;
}) => {
  return (
    <nav className="bg-white-secondary dark:bg-dark-secondary sticky top-0 z-30 flex max-h-20 w-full items-center justify-between px-3 shadow-md">
      <div className={`flex w-full items-center gap-1`}>
        {!isSidebarOpen && (
          <button className="cursor-pointer" type={"button"} onClick={() => toggleSidebar()}>
            <Menu className="text-brand h-8 w-8 dark:text-white" />
          </button>
        )}
      </div>
      <div className={`flex w-full items-center justify-end`}>
        <DarkModeToggle />
        <LanguageDropdown />
        <ProfileDropdown />
      </div>
    </nav>
  );
};

export default Navbar;
