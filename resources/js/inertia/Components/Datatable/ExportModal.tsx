import { DataTableSchema } from "@/Components/Datatable/DataTableUtils";
import Button from "@/Components/ui/Button";
import Modal from "@/Components/ui/Modal";
import DownloadFile from "@/Hooks/DownloadFile";
import Http from "@/Modules/Http/Http";
import { FormEvent, useState } from "react";

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
      Http.make()
        .file()
        .post(exportRoute ?? "", {
          columns: cols,
        }),
    ).then(() => {
      setOpenExport(false);
    });
  };

  return (
    <Modal
      isOpen={openExport}
      onClose={() => {
        setOpenExport(false);
      }}
    >
      <form onSubmit={onSubmit}>
        <div className="grid grid-cols-2 gap-x-2 gap-y-1">
          {exportables
            ? exportables.map((exp, index) => (
                <label
                  className="flex items-center justify-between gap-2 dark:text-white"
                  key={index}
                >
                  {exp}
                  <input
                    type="checkbox"
                    className="accent-primary rounded-md"
                    value={exp as string}
                    name="columns"
                    onChange={(e) => {
                      e.target.checked
                        ? setCols((prev) => {
                            let temp = prev;
                            temp.push(exp as string);
                            return temp;
                          })
                        : setCols((prev) => prev.filter((c) => c != exp));
                    }}
                    defaultChecked={true}
                  />
                </label>
              ))
            : schema.map((item, index) =>
                item.name && item.name != "id" ? (
                  <label
                    className="flex items-center justify-between gap-2"
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
                              temp.push(item.name as string);
                              return temp;
                            })
                          : setCols((prev) =>
                              prev.filter((c) => c != item.name),
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
        <div className="my-5 flex items-center">
          <Button disabled={isLoading}>Export</Button>
        </div>
      </form>
    </Modal>
  );
};

export default ExportModal;
