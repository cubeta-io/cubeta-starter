import { FormEvent, useState } from "react";
import Modal from "../ui/Modal";
import Input from "../form/fields/Input";
import { useForm } from "@inertiajs/react";
import Button from "../ui/Button";
import DownloadFile from "@/Hooks/DownloadFile";

const ImportModal = ({
    openImport,
    setOpenImport,
    revalidate,
    importRoute,
    importExampleRoute,
}: {
    openImport: boolean;
    setOpenImport: (value: boolean | ((prev: boolean) => boolean)) => void;
    revalidate: () => void;
    importRoute: string;
    importExampleRoute?: string;
}) => {
    const { post, setData, errors, processing } = useForm<{
        excel_file?: File;
    }>();

    const { isLoading, downloadFile } = DownloadFile();

    const onSubmit = (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        post(importRoute, {
            onSuccess: () => {
                if (!processing && !isLoading) {
                    revalidate();
                    setOpenImport(false);
                    setData("excel_file", undefined);
                }
            },
        });
    };
    return (
        <Modal
            isOpen={openImport}
            onClose={() => {
                if (!isLoading && !processing) {
                    setOpenImport(false);
                }
            }}
        >
            <form onSubmit={onSubmit}>
                <label>
                    Excel File
                    <Input
                        name={"excel_file"}
                        type="file"
                        label="excel file"
                        onChange={(e) => {
                            setData("excel_file", e.target.files?.[0]);
                        }}
                    />
                </label>
                <div className="flex items-center gap-2">
                    <Button type="submit" disabled={processing}>
                        Import
                    </Button>
                    {importExampleRoute && (
                        <Button
                            color="secondary"
                            type="button"
                            onClick={() => {
                                downloadFile(() => fetch(importExampleRoute));
                            }}
                            disabled={isLoading}
                        >
                            Get Import Example
                        </Button>
                    )}
                </div>
            </form>
        </Modal>
    );
};

export default ImportModal;
