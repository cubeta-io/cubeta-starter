import LanguageDropdown from "@/Components/ui/LanguageDropDown";
import ProfileDropdown from "@/Components/ui/ProfileDropdown";
import Menu from "../icons/Menu";
import DarkModeToggle from "@/Components/ui/DarkModeToggle";

const Navbar = ({
                    isSidebarOpen,
                    toggleSidebar,
                }: {
    isSidebarOpen: boolean;
    toggleSidebar: () => void;
}) => {
    return (
        <nav className="top-0 z-30 sticky flex justify-between items-center bg-white-secondary dark:bg-dark-secondary shadow-md px-3 w-full max-h-20">
            <div className={`flex w-full items-center gap-1`}>
                {!isSidebarOpen && (
                    <button type={"button"} onClick={() => toggleSidebar()}>
                        <Menu className="w-8 h-8 text-brand dark:text-white" />
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
