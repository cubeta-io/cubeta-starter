import { useState } from "react";
import { usePage } from "@inertiajs/react";
import { User } from "@/Models/User";
import { asset } from "@/helper";
import ChevronDown from "../icons/ChevronDown";

const ProfileDropdown = () => {
    const [open, setOpen] = useState(false);
    const { authUser } = usePage().props;

    return (
        <div className={`w-auto`}>
            <button
                className={
                    "focus:outline-none bg-transparent py-2 px-5 inline-flex justify-center items-center rounded-lg text-sm text-center"
                }
                type={"button"}
                onClick={() => setOpen((prevState) => !prevState)}
            >
                <div className="mx-2 border rounded-full">
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
                } z-10 mr-24 bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700`}
            >
                <ul className="shadow-md py-2 h-full text-gray-700 text-sm dark:text-gray-200">
                    <li>
                        <a className="block hover:bg-gray-100 dark:hover:bg-gray-600 px-2 py-2 border border-b-gray-200 rounded-sm dark:hover:text-white">
                            My Profile
                        </a>
                    </li>

                    <li>
                        <a className="block hover:bg-gray-100 dark:hover:bg-gray-600 px-2 py-2 border border-b-gray-200 rounded-sm dark:hover:text-white">
                            Account Settings
                        </a>
                    </li>

                    <li>
                        <a className="block hover:bg-gray-100 dark:hover:bg-gray-600 px-2 py-2 rounded-sm dark:hover:text-white">
                            Sign Out
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    );
};

export default ProfileDropdown;
