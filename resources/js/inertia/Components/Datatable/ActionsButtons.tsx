import {Link, usePage} from "@inertiajs/react";
import Eye from "../icons/Eye";
import Pencil from "../icons/Pencil";
import Trash from "../icons/Trash";
import {swal} from "@/helper";
import {toast} from "react-toastify";
import {MiddlewareProps} from "@/types";

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

    const csrf = usePage<MiddlewareProps>().props.csrfToken;

    return (
        <div className={`flex justify-start gap-3 items-center`}>
            {buttons.includes("show") ? (
                <Link href={sUrl} className="hover:bg-white-secondary p-0.5 rounded-md">
                    <Eye className="w-5 h-5 text-info"/>
                </Link>
            ) : (
                ""
            )}
            {buttons.includes("edit") ? (
                <Link href={eUrl} className="hover:bg-white-secondary p-0.5 rounded-md">
                    <Pencil className="w-5 h-5 text-success"/>
                </Link>
            ) : (
                ""
            )}

            {buttons.includes("delete") ? (
                <button className="hover:bg-white-secondary p-0.5 rounded-md">
                    <Trash
                        className="w-5 h-5 text-danger cursor-pointer"
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
                                                    "There Is Been An Error In Deleting",
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
