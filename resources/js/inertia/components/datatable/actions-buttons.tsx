import { Button } from "@/components/ui/button";
import {
  AlertDialog,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import Http from "@/modules/http/http";
import { Link } from "@inertiajs/react";
import { ReactNode, useState } from "react";
import { toast } from "sonner";
import { Eye, Loader, Pencil, Trash } from "lucide-react";

type Buttons = "delete" | "edit" | "show";

export interface ActionsButtonsProps<Data extends Record<string, any>> {
  data?: Data;
  id?: number | string;
  buttons: Buttons[];
  children?: ReactNode;
  baseUrl: string;
  deleteUrl?: string;
  editUrl?: string;
  showUrl?: string;
  setHidden?: (value: ((prevState: number[]) => number[]) | number[]) => void;
}

function ActionsButtons<Data extends Record<string, any>>({
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
  const [openDelete, setOpenDelete] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const dataId = id ?? data?.id ?? undefined;

  const dUrl = deleteUrl ?? `${baseUrl}/${dataId ?? ""}`;
  const sUrl = showUrl ?? `${baseUrl}/${dataId ?? ""}`;
  const eUrl = editUrl ?? `${baseUrl}/${dataId ?? ""}/edit`;

  const handleDelete = () => {
    setDeleting(true);
    Http.make<boolean>()
      .delete(dUrl)
      .then((res) => {
        if (res.ok()) {
          toast.success("Deleted !");
          if (setHidden) {
            setHidden((prevState) => [dataId, ...prevState]);
          }
        } else {
          toast.error("There Is Been An Error In Deleting");
        }
      })
      .catch((e) => {
        toast.error("There Is Been An Error In Deleting");
        console.error(e);
      })
      .finally(() => {
        setDeleting(false);
      });
  };

  const onConfirmDelete = () => {
    setOpenDelete(false);
    handleDelete();
  };

  return (
    <div className="flex items-center justify-start gap-1">
      {buttons.includes("show") && (
        <Link href={sUrl}>
          <Button size="icon">
            <Eye />
          </Button>
        </Link>
      )}
      {buttons.includes("edit") && (
        <Link href={eUrl}>
          <Button size="icon" variant="outline">
            <Pencil />
          </Button>
        </Link>
      )}

      {buttons.includes("delete") && (
        <AlertDialog open={openDelete} onOpenChange={setOpenDelete}>
          <AlertDialogTrigger
            render={
              <Button size="icon" variant="destructive">
                <Trash />
              </Button>
            }
          />
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Delete Item</AlertDialogTitle>
              <AlertDialogDescription>
                Are you sure you want to delete this item? This action cannot be
                undone.
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <Button
                type="button"
                variant="secondary"
                onClick={() => setOpenDelete(false)}
              >
                Cancel
              </Button>
              <Button
                type="button"
                variant="destructive"
                onClick={onConfirmDelete}
                disabled={deleting}
              >
                Delete
                {deleting && <Loader className={"animate-spin"} />}
              </Button>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      )}
      {children}
    </div>
  );
}

export default ActionsButtons;
