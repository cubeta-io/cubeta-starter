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
        <div className={`w-full flex justify-between items-center my-2`}>
            <div className={"flex gap-1"}>
                {createUrl ? (
                    <Link href={createUrl ?? "#"}>
                        <DocumentPlus
                            className={`h-7 w-7 text-primary hover:text-black dark:hover:text-white`}
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
                                "h-7 w-7 text-info hover:text-black dark:hover:text-white"
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
                                "h-7 w-7 text-secondary hover:text-black dark:hover:text-white "
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
                                "h-7 w-7 text-success hover:text-black dark:hover:text-white"
                            }
                        />
                    </button>
                )}
            </div>
            <div className={"flex gap-2"}>
                <select
                    className="cursor-pointer border-gray-300 py-2 rounded-lg w-full text-gray-700 sm:text-sm dark:bg-gray-800 dark:text-white"
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
