import { translate } from "@/Models/Translatable";
import { getNestedPropertyValue } from "@/helper";
import { TableBodyProps } from "./DataTableUtils";

function TableBody<Data>({
    tableSchema,
    data,
    setHidden,
    revalidate,
    hidden = [],
}: TableBodyProps<Data>) {
    return (
        <tbody className="divide-y divide-gray-200">
            {data?.length ? (
                data?.map((item: any, index: any) => {
                    if (!hidden.includes(item.id ?? index)) {
                        return (
                            <tr key={`${index}-${item.label}`}>
                                {tableSchema.map((schema, index) => {
                                    if (!schema.render && schema.name) {
                                        return (
                                            <td
                                                key={`${schema.label} - ${index}`}
                                                className={
                                                    schema.cellProps
                                                        ?.className ??
                                                    "whitespace-nowrap px-4 py-2 font-medium text-gray-900 border"
                                                }
                                                {...schema.cellProps}
                                            >
                                                {schema?.translatable
                                                    ? translate(
                                                          getNestedPropertyValue(
                                                              item,
                                                              schema.name as string
                                                          )
                                                      )
                                                    : getNestedPropertyValue(
                                                          item,
                                                          schema.name as string
                                                      ) ?? "No Data"}
                                            </td>
                                        );
                                    } else if (schema.render && schema.name) {
                                        return (
                                            <td
                                                key={`${schema.label} - ${index}`}
                                                className={
                                                    schema.cellProps
                                                        ?.className ??
                                                    "whitespace-nowrap px-4 py-2 font-medium text-gray-900 border"
                                                }
                                                {...schema.cellProps}
                                            >
                                                {schema.render(
                                                    schema?.translatable
                                                        ? translate(
                                                              getNestedPropertyValue(
                                                                  item,
                                                                  schema.name as string
                                                              )
                                                          )
                                                        : getNestedPropertyValue(
                                                              item,
                                                              schema.name as string
                                                          ) ?? "No Data",
                                                    item,
                                                    setHidden,
                                                    revalidate
                                                )}
                                            </td>
                                        );
                                    } else if (schema.render) {
                                        return (
                                            <td
                                                key={`${schema.label} - ${index}`}
                                                className={
                                                    schema.cellProps
                                                        ?.className ??
                                                    "whitespace-nowrap px-4 py-2 font-medium text-gray-900 border"
                                                }
                                                {...schema.cellProps}
                                            >
                                                {schema.render(
                                                    undefined,
                                                    item,
                                                    setHidden,
                                                    revalidate
                                                )}
                                            </td>
                                        );
                                    } else
                                        return (
                                            <td
                                                key={index}
                                                className={
                                                    schema.cellProps
                                                        ?.className ??
                                                    "whitespace-nowrap px-4 py-2 font-medium text-gray-900 border"
                                                }
                                                {...schema.cellProps}
                                            >
                                                No Data
                                            </td>
                                        );
                                })}
                            </tr>
                        );
                    }
                })
            ) : (
                <tr>
                    <td
                        colSpan={tableSchema.length}
                        className={"text-center p-3"}
                    >
                        No Data
                    </td>
                </tr>
            )}
        </tbody>
    );
}

export default TableBody;
