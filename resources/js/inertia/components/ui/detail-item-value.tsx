import React, { ReactNode } from "react";
import { Badge } from "@/components/ui/badge";
import { FieldDescription } from "@/components/ui/field";
import { useTranslation } from "@/hooks/use-translation";

interface ValueProps extends React.ComponentProps<typeof FieldDescription> {
  value?: unknown;
  children?: ReactNode;
  html?: boolean;
}

export const DetailItemValue: React.FC<ValueProps> = ({
  value,
  children,
  html,
  ...props
}) => {
  const { t } = useTranslation();
  let showedValue = value;
  if (typeof value == "string" && html) {
    showedValue = <div dangerouslySetInnerHTML={{ __html: value }} />;
  } else if (value === undefined || value === null) {
    showedValue = <Badge>{t("dashboard.table.no_data")}</Badge>;
  } else if (value === 0 || Number.isNaN(value)) {
    showedValue = 0;
  } else if (value === false) {
    showedValue = t("dashboard.boolean.no");
  } else if (value === "") {
    showedValue = <Badge>{t("dashboard.table.no_data")}</Badge>;
  } else if (
    typeof value == "string" &&
    (value == "undefined" || value == "null")
  ) {
    showedValue = <Badge>{t("dashboard.table.no_data")}</Badge>;
  } else if (typeof value == "string" && value.includes("NaN")) {
    showedValue = 0;
  }

  return (
    <FieldDescription {...props}>
      {!children ? <span>{showedValue as ReactNode}</span> : children}
    </FieldDescription>
  );
};
