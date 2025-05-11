import ChevronUp from "@/Components/icons/ChevronUp";
import ChevronDown from "@/Components/icons/ChevronDown";
import { TableHeadProps } from "@/Components/Datatable/DataTableUtils";

function TableHead<Data>({
  schema,
  sortDir,
  setSortDir,
  sortCol,
  setSortCol,
}: TableHeadProps<Data>) {
  return (
    <thead className="ltr:text-left rtl:text-right dark:text-white">
      <tr>
        {schema.map((header) => (
          <th
            key={header.label}
            onClick={() => {
              if (header.name && header.sortable) {
                setSortDir((prevState) => {
                  if (prevState == "asc") {
                    return "desc";
                  } else return "asc";
                });

                setSortCol(header.name as string);
              }
            }}
            className={
              header.headerProps?.className ??
              `whitespace-nowrap px-4 py-2 font-medium text-gray-900 dark:text-white ${
                header.sortable ? "cursor-pointer" : ""
              }`
            }
            {...header.headerProps}
          >
            <div className={`flex items-center justify-between gap-2`}>
              {header.label}

              {header.sortable ? (
                <div className={`flex flex-col gap-0`}>
                  <ChevronUp
                    className={`h-3 w-3 ${
                      sortDir == "asc" && sortCol == header.name
                        ? "fill-primary"
                        : ""
                    }`}
                  />
                  <ChevronDown
                    className={`h-3 w-3 ${
                      sortDir == "desc" && sortCol == header.name
                        ? "fill-primary"
                        : ""
                    }`}
                  />
                </div>
              ) : null}
            </div>
          </th>
        ))}
      </tr>
    </thead>
  );
}

export default TableHead;
