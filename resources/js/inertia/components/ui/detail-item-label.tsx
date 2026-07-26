import React from "react";
import { FieldLabel } from "@/components/ui/field";

interface LabelProps extends React.ComponentProps<typeof FieldLabel> {
  label?: string | any;
}

export const DetailItemLabel: React.FC<LabelProps> = ({ label, ...props }) => {
  return <FieldLabel {...props}>{label}</FieldLabel>;
};
