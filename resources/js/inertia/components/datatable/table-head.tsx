import { ChevronDown, ChevronUp } from "lucide-react";
import { TableHeadProps } from "@/components/datatable/types";
import {
  TableHead as ShadcnTableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";

function TableHead<Data>({
  schema,
  sortDir,
  setSortDir,
  sortCol,
  setSortCol,
}: TableHeadProps<Data>) {
  return (
    <TableHeader>
      <TableRow>
        {schema.map((header) => (
          <ShadcnTableHead
            key={header.label}
            onClick={() => {
              if (header.name && header.sortable) {
                setSortDir((prevState) =>
                  prevState === "asc" ? "desc" : "asc",
                );
                setSortCol(header.name as string);
              }
            }}
            className={header.sortable ? "cursor-pointer" : undefined}
            {...header.headerProps}
          >
            <div className="flex items-center justify-between gap-2">
              {header.label}
              {header.sortable ? (
                <div className="flex flex-col gap-0">
                  <ChevronUp
                    className={`h-3 w-3 ${
                      sortDir === "asc" && sortCol === header.name
                        ? "fill-primary"
                        : ""
                    }`}
                  />
                  <ChevronDown
                    className={`h-3 w-3 ${
                      sortDir === "desc" && sortCol === header.name
                        ? "fill-primary"
                        : ""
                    }`}
                  />
                </div>
              ) : null}
            </div>
          </ShadcnTableHead>
        ))}
      </TableRow>
    </TableHeader>
  );
}

export default TableHead;
