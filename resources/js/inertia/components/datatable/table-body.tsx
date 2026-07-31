import { TableBodyProps } from "@/components/datatable/types";
import { getNestedPropertyValue } from "@/helper";
import { translate } from "@/models/translatable";
import {
  TableBody as ShadcnTableBody,
  TableCell,
  TableRow,
} from "@/components/ui/table";

function TableBody<Data>({
  tableSchema,
  data,
  setHidden,
  revalidate,
  hidden = [],
}: TableBodyProps<Data>) {
  return (
    <ShadcnTableBody>
      {data?.length ? (
        data.map((item: any, index: any) => {
          if (hidden.includes(item.id ?? index)) {
            return null;
          }

          return (
            <TableRow key={`${index}-${item.label}`}>
              {tableSchema.map((schema, schemaIndex) => {
                const key = `${schema.label}-${schemaIndex}`;
                const cellClassName =
                  schema.cellProps?.className ??
                  "whitespace-nowrap px-4 py-2 font-medium";

                const value = schema.name
                  ? schema.translatable
                    ? translate(
                        getNestedPropertyValue(item, schema.name as string),
                      )
                    : (getNestedPropertyValue(item, schema.name as string) ??
                      "No Data")
                  : undefined;

                if (!schema.render && schema.name) {
                  return (
                    <TableCell
                      key={key}
                      className={cellClassName}
                      {...schema.cellProps}
                    >
                      {value}
                    </TableCell>
                  );
                }

                if (schema.render && schema.name) {
                  return (
                    <TableCell
                      key={key}
                      className={cellClassName}
                      {...schema.cellProps}
                    >
                      {schema.render(value, item, setHidden, revalidate)}
                    </TableCell>
                  );
                }

                if (schema.render) {
                  return (
                    <TableCell
                      key={key}
                      className={cellClassName}
                      {...schema.cellProps}
                    >
                      {schema.render(undefined, item, setHidden, revalidate)}
                    </TableCell>
                  );
                }

                return (
                  <TableCell
                    key={key}
                    className={cellClassName}
                    {...schema.cellProps}
                  >
                    No Data
                  </TableCell>
                );
              })}
            </TableRow>
          );
        })
      ) : (
        <TableRow>
          <TableCell colSpan={tableSchema.length} className="p-3 text-center">
            No Data
          </TableCell>
        </TableRow>
      )}
    </ShadcnTableBody>
  );
}

export default TableBody;
