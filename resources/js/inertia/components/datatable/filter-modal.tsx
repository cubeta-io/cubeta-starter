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
  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogTrigger
        render={
          <Button size="icon" variant="secondary" type="button">
            <FilterIcon />
          </Button>
        }
      />
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Filters</DialogTitle>
        </DialogHeader>
        {children}
        <DialogFooter>
          <Button onClick={onReset} variant="destructive" type="button">
            Reset Filters
          </Button>
          <Button
            onClick={() => {
              onApply();
              onOpenChange(false);
            }}
            type="button"
          >
            Apply
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
}

export default FilterModal;
