import { Link, usePage } from "@inertiajs/react";
import Eye from "../icons/Eye";
import Pencil from "../icons/Pencil";
import Trash from "../icons/Trash";
import { swal } from "@/helper";
import { toast } from "react-toastify";
import { PageProps } from "@/types";

type Buttons = "delete" | "edit" | "show";

export interface ActionsButtonsProps<Data> {
    data?: Data;
    id?: number | string;
    buttons: Buttons[];
    children?: React.JSX.Element | undefined;
    baseUrl: string;
    deleteUrl?: string;
    editUrl?: string;
    showUrl?: string;
    setHidden?: (value: ((prevState: number[]) => number[]) | number[]) => void;
}

function ActionsButtons<Data>({
    data,
    id,
    buttons,
    baseUrl,
    deleteUrl,
    showUrl,
    editUrl,
    setHidden,
    children,
}: ActionsButtonsProps<Data>) {
    const dataId = id ?? data?.id ?? undefined;

    const dUrl = deleteUrl ?? `${baseUrl}/${dataId ?? ""}`; // delete url
    const sUrl = showUrl ?? `${baseUrl}/${dataId ?? ""}`; // show url
    const eUrl = editUrl ?? `${baseUrl}/${dataId ?? ""}/edit` + ""; // edit url

    const csrf = usePage<PageProps>().props.csrfToken;

    return (
        <div className={`flex justify-between items-center`}>
            {buttons.includes("show") ? (
                <Link
                    href={sUrl}
                    className="bg-gray-100 hover:bg-gray-200 p-1 rounded-md"
                >
                    <Eye className="w-6 h-6 text-info" />
                </Link>
            ) : (
                ""
            )}
            {buttons.includes("edit") ? (
                <Link
                    href={eUrl}
                    className="bg-gray-100 hover:bg-gray-200 p-1 rounded-md"
                >
                    <Pencil className="w-6 h-6 text-success" />
                </Link>
            ) : (
                ""
            )}

            {buttons.includes("delete") ? (
                <button className="bg-gray-100 hover:bg-gray-200 p-1 rounded-md">
                    <Trash
                        className="w-6 h-6 text-danger"
                        onClick={() => {
                            swal.fire({
                                title: "Do you want to Delete this item ?",
                                showDenyButton: true,
                                showCancelButton: true,
                                confirmButtonText: "Yes",
                                denyButtonText: `No`,
                                confirmButtonColor: "#007BFF",
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    if (dataId) {
                                        fetch(dUrl, {
                                            method: "DELETE",
                                            headers: {
                                                "X-CSRF-TOKEN": csrf,
                                            },
                                        })
                                            .then(() => {
                                                toast.success("Deleted !");
                                                if (setHidden) {
                                                    setHidden((prevState) => [
                                                        dataId,
                                                        ...prevState,
                                                    ]);
                                                }
                                            })
                                            .catch(() => {
                                                toast.error(
                                                    "There Is Been An Error In Deleting"
                                                );
                                            });
                                    }
                                } else if (result.isDenied) {
                                    toast.info("Didn't Delete");
                                }
                            });
                        }}
                    />
                </button>
            ) : (
                ""
            )}
            {children}
        </div>
    );
}

export default ActionsButtons;
