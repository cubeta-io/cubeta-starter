import { useEffect, useState } from "react";
import { Link, usePage } from "@inertiajs/react";
import { User } from "@/Models/User";
import { asset } from "@/helper";
import ChevronDown from "../icons/ChevronDown";

const ProfileDropdown = () => {
    const [open, setOpen] = useState(false);
    const { authUser } = usePage().props;

    useEffect(() => {
        document.addEventListener("mousedown", (e) => {
            setOpen(false);
        });
        return () => {
            document.removeEventListener("mousedown", (e) => {
                setOpen(false);
            });
        };
    }, []);

    return (
        <div className={`w-auto relative`}>
            <button
                className={
                    "focus:outline-none bg-transparent py-2 px-5 inline-flex dark:text-white justify-center items-center rounded-lg text-sm text-center"
                }
                type={"button"}
                onClick={() => setOpen((prevState) => !prevState)}
            >
                <div className="mx-2 rounded-full">
                    <img
                        className="rounded-full h-12"
                        src={asset("/images/profile-img.jpg")}
                        alt=""
                    />
                </div>

                {(authUser as User)?.name ?? "App Admin"}

                <ChevronDown className="w-4 h-4 ms-3" />
            </button>

            <div
                className={`${
                    open ? "absolute" : "hidden"
                } z-10 start-5 bg-white-secondary dark:bg-dark-secondary rounded-lg shadow w-44`}
            >
                <ul className="shadow-md h-full text-gray-700 text-sm dark:text-white">
                    <li>
                        <Link className="cursor-pointer block hover:bg-gray-50 dark:hover:text-black p-2 rounded-md">
                            My Profile
                        </Link>
                    </li>

                    <li>
                        <a className="cursor-pointer block hover:bg-gray-50 dark:hover:text-black p-2 rounded-md">
                            Account Settings
                        </a>
                    </li>

                    <li>
                        <a className="cursor-pointer block hover:bg-gray-50 dark:hover:text-black p-2 rounded-md">
                            Sign Out
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    );
};

export default ProfileDropdown;
