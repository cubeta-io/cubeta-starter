import { TableSchema } from "@/components/datatable/types";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Label } from "@/components/ui/label";
import useDownloadFile from "@/hooks/use-download-file";
import Http from "@/modules/http/http";
import React, { useState } from "react";
import { toTitleCase } from "@/helper";
import { TableIcon } from "lucide-react";
import { useTranslation } from "@/hooks/use-translation";

interface ExportModalProps {
  schema: TableSchema<any>[];
  exportRoute?: string;
  exportables?: string[];
}

const ExportModal = ({
  schema,
  exportRoute,
  exportables,
}: ExportModalProps) => {
  const { t } = useTranslation();
  const [open, setOpen] = useState(false);
  const { isLoading, downloadFile } = useDownloadFile();

  const columns = exportables
    ? exportables.map((name) => ({
        label:
          schema.find((column) => column.name === name)?.label ??
          toTitleCase(name),
        value: name,
      }))
    : schema
        .filter((col) => col.name !== undefined && col.name !== "id")
        .map((col) => ({
          label: col.label ?? toTitleCase(col.name as string),
          value: col.name as string,
        }));

  const [selectedColumns, setSelectedColumns] = useState<string[]>(
    columns.map((col) => col.value),
  );

  const toggleColumn = (column: string, checked: boolean) => {
    setSelectedColumns((prev) =>
      checked ? [...prev, column] : prev.filter((col) => col !== column),
    );
  };

  const onSubmit = (e: React.SubmitEvent<HTMLFormElement>) => {
    e.preventDefault();
    downloadFile(() =>
      Http.make()
        .file()
        .post(exportRoute ?? "", {
          columns: selectedColumns,
        }),
    ).then(() => {
      setOpen(false);
    });
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger
        render={
          <Button
            type="button"
            size="icon"
            variant="outline"
            aria-label={t("dashboard.actions.export")}
          >
            <TableIcon />
          </Button>
        }
      />
      <DialogContent>
        <form onSubmit={onSubmit}>
          <DialogHeader>
            <DialogTitle>{t("dashboard.actions.export")}</DialogTitle>
          </DialogHeader>
          <div className="grid grid-cols-2 gap-4 py-4">
            {columns.map((column) => (
              <Label
                key={column.value}
                className="flex cursor-pointer items-center justify-between gap-2 font-normal"
              >
                {column.label}
                <Checkbox
                  name="columns"
                  value={column.value}
                  checked={selectedColumns.includes(column.value)}
                  onCheckedChange={(checked) =>
                    toggleColumn(column.value, checked)
                  }
                />
              </Label>
            ))}
          </div>
          <DialogFooter>
            <Button disabled={isLoading} type="submit">
              {t("dashboard.actions.export")}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
};

export default ExportModal;
