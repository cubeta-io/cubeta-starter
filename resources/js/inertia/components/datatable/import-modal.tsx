import Input from "@/components/form/fields/input";
import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { useForm } from "@inertiajs/react";
import { useState } from "react";
import useDownloadFile from "@/hooks/use-download-file";
import { DownloadIcon, Loader } from "lucide-react";

interface ImportModalProps {
  revalidate: () => void;
  importRoute: string;
  importExampleRoute?: string;
}

const ImportModal = ({
  revalidate,
  importRoute,
  importExampleRoute,
}: ImportModalProps) => {
  const [open, setOpen] = useState(false);

  const { post, setData, processing } = useForm<{
    excel_file?: File;
  }>();

  const { isLoading, downloadFile } = useDownloadFile();

  const onSubmit = () => {
    post(importRoute, {
      onSuccess: () => {
        if (!processing && !isLoading) {
          revalidate();
          setOpen(false);
          setData("excel_file", undefined);
        }
      },
    });
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger
        render={
          <Button type="button" size="icon" variant="secondary">
            <DownloadIcon />
          </Button>
        }
      />
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Import from excel file</DialogTitle>
        </DialogHeader>
        <Input
          name="excel_file"
          type="file"
          label="Excel File"
          onChange={(e) => {
            setData("excel_file", e.target.files?.[0]);
          }}
        />
        <DialogFooter>
          <Button
            type="button"
            variant="destructive"
            onClick={() => setOpen(false)}
          >
            Cancel
          </Button>
          <Button type="button" disabled={processing} onClick={onSubmit}>
            Import
            {processing && <Loader className={"animate-spin"} />}
          </Button>
          {importExampleRoute && (
            <Button
              type="button"
              variant="secondary"
              disabled={isLoading}
              onClick={async () => {
                await downloadFile(() => fetch(importExampleRoute));
                setOpen(false);
              }}
            >
              Get import example
              {isLoading && <Loader className={"animate-spin"} />}
            </Button>
          )}
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
};

export default ImportModal;
