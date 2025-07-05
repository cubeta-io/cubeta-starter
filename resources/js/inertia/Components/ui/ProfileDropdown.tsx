import { useEffect, useRef, useState } from "react";
import { Link, usePage } from "@inertiajs/react";
import User from "@/Models/User";
import { asset } from "@/helper";
import ChevronDown from "@/Components/icons/ChevronDown";

const ProfileDropdown = () => {
  const [open, setOpen] = useState(false);
  const { authUser } = usePage().props;
  const dropdownRef = useRef<HTMLDivElement>(null);

  const handleClickOutside = (event: MouseEvent) => {
    if (
      dropdownRef.current &&
      !dropdownRef.current.contains(event.target as Node)
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

  return (
    <div ref={dropdownRef} className="relative w-auto">
      <button
        className="inline-flex items-center justify-center rounded-lg bg-transparent px-5 py-2 text-center text-sm focus:outline-none dark:text-white"
        type="button"
        onClick={() => setOpen((prevState) => !prevState)}
      >
        <div className="mx-2 rounded-full">
          <img
            className="h-12 rounded-full"
            src={asset("/images/profile-img.jpg")}
            alt=""
          />
        </div>
        {(authUser as User)?.first_name ??
          undefined + (authUser as User)?.last_name ??
          undefined ??
          "App Admin"}
        <ChevronDown className="ms-3 h-4 w-4" />
      </button>

      <div
        className={`${
          open ? "absolute" : "hidden"
        } bg-white-secondary dark:bg-dark-secondary start-5 z-10 w-44 rounded-lg shadow`}
      >
        <ul className="h-full text-sm text-gray-700 shadow-md dark:text-white">
          <li>
            <Link
              id="user-details"
              href={"#"}
              className="block cursor-pointer rounded-md p-2 hover:bg-gray-50 dark:hover:text-black"
            >
              My Profile
            </Link>
          </li>
          <li>
            <Link
              id="logout"
              href={"#"}
              className="block cursor-pointer rounded-md p-2 hover:bg-gray-50 dark:hover:text-black"
            >
              Sign Out
            </Link>
          </li>
        </ul>
      </div>
    </div>
  );
};

export default ProfileDropdown;
