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
import { useTranslation } from "@/hooks/use-translation";

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
  const { t } = useTranslation();
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
          toast.success(t("dashboard.delete.success"));
          if (setHidden) {
            setHidden((prevState) => [dataId, ...prevState]);
          }
        } else {
          toast.error(t("dashboard.delete.error"));
        }
      })
      .catch((e) => {
        toast.error(t("dashboard.delete.error"));
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
          <Button size="icon" aria-label={t("dashboard.actions.show")}>
            <Eye />
          </Button>
        </Link>
      )}
      {buttons.includes("edit") && (
        <Link href={eUrl}>
          <Button
            size="icon"
            variant="outline"
            aria-label={t("dashboard.actions.edit")}
          >
            <Pencil />
          </Button>
        </Link>
      )}

      {buttons.includes("delete") && (
        <AlertDialog open={openDelete} onOpenChange={setOpenDelete}>
          <AlertDialogTrigger
            render={
              <Button
                size="icon"
                variant="destructive"
                aria-label={t("dashboard.actions.delete")}
              >
                <Trash />
              </Button>
            }
          />
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>{t("dashboard.delete.title")}</AlertDialogTitle>
              <AlertDialogDescription>
                {t("dashboard.delete.confirmation")}
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <Button
                type="button"
                variant="secondary"
                onClick={() => setOpenDelete(false)}
              >
                {t("dashboard.actions.cancel")}
              </Button>
              <Button
                type="button"
                variant="destructive"
                onClick={onConfirmDelete}
                disabled={deleting}
              >
                {t("dashboard.actions.delete")}
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
