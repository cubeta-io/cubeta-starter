import { usePage } from "@inertiajs/react";
import Modal from "../ui/Modal";
import { DataTableSchema } from "./DataTableUtils";
import DownloadFile from "@/Hooks/DownloadFile";
import { FormEvent, useState } from "react";
import Button from "../ui/Button";
import { MiddlewareProps } from "@/types";

const ExportModal = ({
                         openExport,
                         setOpenExport,
                         schema,
                         exportRoute,
                         exportables = undefined,
                     }: {
    openExport: boolean;
    setOpenExport: (value: boolean | ((prev: boolean) => boolean)) => void;
    schema: DataTableSchema<any>[];
    exportRoute?: string;
    exportables?: string[];
}) => {
    const csrf = usePage<MiddlewareProps>().props.csrfToken;
    const [cols, setCols] = useState<string[]>(
        exportables
            ? exportables
            : schema
                .filter((col) => col.name != undefined && col.name != "id")
                .map((c) => c.name as string),
    );
    const { isLoading, downloadFile } = DownloadFile();

    const onSubmit = (e: FormEvent) => {
        e.preventDefault();
        downloadFile(() =>
            fetch(exportRoute ?? "", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrf,
                    "Content-Type": "application/html",
                },
                body: JSON.stringify({ columns: cols }),
            }),
        );
        if (!isLoading) {
            setOpenExport(false);
        }
    };

    return (
        <Modal
            isOpen={openExport}
            onClose={() => {
                setOpenExport(false);
            }}
        >
            <form onSubmit={onSubmit}>
                <div className="grid grid-cols-2">
                    {exportables
                        ? exportables.map((exp, index) => (
                            <label
                                className="flex items-center gap-2 dark:text-white"
                                key={index}
                            >
                                {exp}
                                <input
                                    type="checkbox"
                                    className="rounded-md accent-primary"
                                    value={exp as string}
                                    name="columns"
                                    onChange={(e) => {
                                        e.target.checked
                                            ? setCols((prev) => {
                                                let temp = prev;
                                                temp.push(exp as string);
                                                return temp;
                                            })
                                            : setCols((prev) =>
                                                prev.filter(
                                                    (c) => c != exp,
                                                ),
                                            );
                                    }}
                                    defaultChecked={true}
                                />
                            </label>
                        ))
                        : schema.map((item, index) =>
                            item.name && item.name != "id" ? (
                                <label
                                    className="flex items-center gap-2"
                                    key={index}
                                >
                                    {item.label ?? item.name}
                                    <input
                                        type="checkbox"
                                        className="rounded-md"
                                        value={item.name as string}
                                        name="columns"
                                        onChange={(e) => {
                                            e.target.checked
                                                ? setCols((prev) => {
                                                    let temp = prev;
                                                    temp.push(
                                                        item.name as string,
                                                    );
                                                    return temp;
                                                })
                                                : setCols((prev) =>
                                                    prev.filter(
                                                        (c) =>
                                                            c != item.name,
                                                    ),
                                                );
                                        }}
                                        defaultChecked={true}
                                    />
                                </label>
                            ) : (
                                ""
                            ),
                        )}
                </div>
                <div className="flex items-center my-3">
                    <Button disabled={isLoading}>Export</Button>
                </div>
            </form>
        </Modal>
    );
};

export default ExportModal;
