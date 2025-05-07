import { Link } from "@inertiajs/react";
import { TableActionsProps } from "@/Components/Datatable/DataTableUtils";
import DocumentPlus from "@/Components/icons/DocumentPlus";
import Filter from "@/Components/icons/Filter";
import ArrowDownTray from "@/Components/icons/ArrowDownTray";
import TableCells from "@/Components/icons/TableCells";

function TableActions({
  createUrl,
  setOpenFilter,
  setPage,
  perPage,
  setPerPage,
  setSearch,
  search,
  filter,
  setOpenImport,
  setOpenExport,
  exportable = false,
  importable = false,
}: TableActionsProps) {
  return (
    <div className={`my-2 flex w-full items-center justify-between`}>
      <div className={"flex gap-1"}>
        {createUrl ? (
          <Link href={createUrl ?? "#"}>
            <DocumentPlus
              className={`text-primary h-7 w-7 hover:text-black dark:hover:text-white`}
            />
          </Link>
        ) : (
          ""
        )}
        {filter ? (
          <button
            type={"button"}
            onClick={() => setOpenFilter((prevState) => !prevState)}
            className={"cursor-pointer"}
          >
            <Filter
              className={
                "text-info h-7 w-7 hover:text-black dark:hover:text-white"
              }
            />
          </button>
        ) : (
          ""
        )}
        {importable && (
          <button
            onClick={() => setOpenImport((prev) => !prev)}
            className={"cursor-pointer"}
          >
            <ArrowDownTray
              className={
                "text-secondary h-7 w-7 hover:text-black dark:hover:text-white"
              }
            />
          </button>
        )}
        {exportable && (
          <button
            onClick={() => setOpenExport((prev) => !prev)}
            className={"cursor-pointer"}
          >
            <TableCells
              className={
                "text-success h-7 w-7 hover:text-black dark:hover:text-white"
              }
            />
          </button>
        )}
      </div>
      <div className={"flex gap-2"}>
        <select
          className="w-full cursor-pointer rounded-lg border-gray-300 py-2 text-gray-700 sm:text-sm dark:bg-gray-800 dark:text-white"
          onChange={(e) => {
            setPage(1);
            setSearch("");
            setPerPage(parseInt(e.target.value));
          }}
          value={perPage}
        >
          <option value={10}>10</option>
          <option value={25}>25</option>
          <option value={50}>50</option>
          <option value={75}>75</option>
          <option value={500}>500</option>
        </select>

        <input
          type="text"
          id="Search"
          placeholder="Search for..."
          className="w-full rounded-md py-2.5 pe-10 shadow-sm sm:text-sm dark:bg-gray-800 dark:text-white"
          value={search}
          onChange={(e) => {
            setSearch(e.target.value);
            setPage(1);
          }}
        />
      </div>
    </div>
  );
}

export default TableActions;
