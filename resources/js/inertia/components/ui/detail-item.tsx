import { Field } from "@/components/ui/field";
import React from "react";
import { DetailItemLabel } from "@/components/ui/detail-item-label";
import { DetailItemValue } from "@/components/ui/detail-item-value";

interface Props extends React.ComponentProps<typeof Field> {
  label: string;
  value: unknown;
}

const DetailItem: React.FC<Props> = ({
  label,
  value,
  orientation,
  ...props
}) => {
  return (
    <Field orientation={orientation ?? "horizontal"} {...props}>
      <DetailItemLabel label={label} />
      <DetailItemValue value={value} />
    </Field>
  );
};

export default DetailItem;
