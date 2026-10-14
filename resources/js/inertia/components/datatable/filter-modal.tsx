import { Button } from "@/components/ui/button";
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import React from "react";
import { FilterIcon } from "lucide-react";
import { useTranslation } from "@/hooks/use-translation";

interface FilterModalProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  children?: React.ReactNode;
  onReset: () => void;
  onApply: () => void;
}

function FilterModal({
  open,
  onOpenChange,
  children,
  onReset,
  onApply,
}: FilterModalProps) {
  const { t } = useTranslation();
  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogTrigger
        render={
          <Button
            size="icon"
            variant="secondary"
            type="button"
            aria-label={t("dashboard.table.filters")}
          >
            <FilterIcon />
          </Button>
        }
      />
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{t("dashboard.table.filters")}</DialogTitle>
        </DialogHeader>
        {children}
        <DialogFooter>
          <Button onClick={onReset} variant="destructive" type="button">
            {t("dashboard.actions.reset_filters")}
          </Button>
          <Button
            onClick={() => {
              onApply();
              onOpenChange(false);
            }}
            type="button"
          >
            {t("dashboard.actions.apply")}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}

export default FilterModal;
