import React, { ReactNode } from "react";
import { usePage } from "@inertiajs/react";
import { toast } from "sonner";
import { Toaster } from "@/components/ui/sonner";

const ToasterProvider = ({ children }: { children: ReactNode }) => {
  const { flash } = usePage();
  if (flash.success) {
    toast.success(flash.success);
  }

  if (flash.error) {
    toast.error(flash.error);
  }
  return (
    <>
      <Toaster />
      {children}
    </>
  );
};

export default ToasterProvider;
