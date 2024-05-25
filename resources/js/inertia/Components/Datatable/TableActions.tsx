import { Link } from "@inertiajs/react";
import Button from "../ui/Button";
import DocumentPlus from "../icons/DocumentPlus";
import { TableActionsProps } from "./DataTableUtils";
import Filter from "../icons/Filter";

function TableActions({
    createUrl,
    setOpenFilter,
    setPage,
    perPage,
    setPerPage,
    setSearch,
    search,
    filter,
}: TableActionsProps) {
    return (
        <div className={`w-full flex justify-between items-center my-2`}>
            <div className={"flex gap-1"}>
                {createUrl ? (
                    <Link href={createUrl ?? "#"}>
                        <Button>
                            <DocumentPlus
                                className={`h-6 w-6 hover:text-primary`}
                            />
                        </Button>
                    </Link>
                ) : (
                    false
                )}
                {filter ? (
                    <div>
                        <Button
                            onClick={() =>
                                setOpenFilter((prevState) => !prevState)
                            }
                        >
                            <Filter />
                        </Button>
                    </div>
                ) : (
                    ""
                )}
            </div>
            <div className={"flex gap-2"}>
                <select
                    className="border-gray-300 py-2 rounded-lg w-full text-gray-700 sm:text-sm"
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
                    className="border-gray-200 shadow-sm py-1 rounded-md w-full sm:text-sm"
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
