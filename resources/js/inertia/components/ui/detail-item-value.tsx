import React, { ReactNode } from "react";
import { Badge } from "@/components/ui/badge";
import { FieldDescription } from "@/components/ui/field";

interface ValueProps extends React.ComponentProps<typeof FieldDescription> {
  value?: unknown;
  children?: ReactNode;
}

export const DetailItemValue: React.FC<ValueProps> = ({
  value,
  children,
  ...props
}) => {
  let showedValue = value;
  if (value === undefined || value === null) {
    showedValue = <Badge>{"No data"}</Badge>;
  } else if (value === 0 || Number.isNaN(value)) {
    showedValue = 0;
  } else if (value === false) {
    showedValue = "false";
  } else if (value === "") {
    showedValue = <Badge>{"No data"}</Badge>;
  } else if (
    typeof value == "string" &&
    (value?.includes("undefined") || value?.includes("null"))
  ) {
    showedValue = <Badge>{"No data"}</Badge>;
  } else if (typeof value == "string" && value.includes("NaN")) {
    showedValue = 0;
  }

  return (
    <FieldDescription {...props}>
      {!children ? <span>{showedValue as ReactNode}</span> : children}
    </FieldDescription>
  );
};
