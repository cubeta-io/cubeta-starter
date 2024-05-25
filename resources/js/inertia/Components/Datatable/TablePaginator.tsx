import ChevronLeft from "../icons/ChevronLeft";
import ChevronRight from "../icons/ChevronRight";
import { TablePaginatorProps } from "./DataTableUtils";

function TablePaginator<ApiResponse>({
    response,
    page,
    setPage,
    getTotalPages,
    getNextPage,
    getPreviousPage,
    getTotalRecords = undefined,
    isFirst = undefined,
    isLast = undefined,
}: TablePaginatorProps<ApiResponse>) {
    const paginationArray = [...Array(getTotalPages(response) ?? 0)];

    const setNextPage = () => {
        if (getNextPage) {
            setPage(getNextPage(response, page));
        } else {
            setPage((old) => old + 1);
        }
    };

    const setPrevPage = () => {
        if (getPreviousPage) {
            setPage(getPreviousPage(response, page));
        } else {
            setPage((old) => Math.max(old - 1, 0));
        }
    };

    return (
        <div className="flex justify-between border-gray-200 px-4 py-2 border-t rounded-b-lg">
            {getTotalRecords && (
                <div className={"justify-start"}>
                    Total Records : {getTotalRecords(response)}
                </div>
            )}
            <ol className="flex justify-end items-center gap-1 font-medium text-xs">
                <li>
                    <button
                        onClick={() => setPrevPage()}
                        disabled={isFirst ? isFirst(response) : page <= 1}
                        className="inline-flex justify-center items-center border-gray-100 bg-white border rounded text-gray-900 size-8 rtl:rotate-180"
                    >
                        <span className="sr-only">Prev Page</span>
                        <ChevronLeft />
                    </button>
                </li>

                {paginationArray.map((_e, index) => {
                    if (index < 3 || index >= paginationArray.length - 1) {
                        return (
                            <li key={`page-${index + 1}`}>
                                <button
                                    onClick={() => setPage(index + 1)}
                                    className={`block size-8 rounded border border-gray-100 text-center leading-8  ${
                                        index + 1 == page
                                            ? "bg-primary text-white"
                                            : "bg-white text-gray-900"
                                    }`}
                                >
                                    {index + 1}
                                </button>
                            </li>
                        );
                    } else if (
                        (index === 3 && page > 5) ||
                        (index === paginationArray.length - 2 &&
                            page < paginationArray.length - 4)
                    ) {
                        return (
                            <li key={`page-${index + 1}`}>
                                <span>...</span>
                            </li>
                        );
                    } else if (index >= page - 2 && index <= page + 1) {
                        return (
                            <li key={`page-${index + 1}`}>
                                <button
                                    onClick={() => setPage(index + 1)}
                                    className={`block size-8 rounded border border-gray-100 text-center leading-8 ${
                                        index + 1 == page
                                            ? "bg-primary text-white"
                                            : "bg-white text-gray-900"
                                    }`}
                                >
                                    {index + 1}
                                </button>
                            </li>
                        );
                    } else return null;
                })}
                <li>
                    <button
                        type={"button"}
                        onClick={() => {
                            setNextPage();
                        }}
                        disabled={
                            isLast
                                ? isLast(response)
                                : getTotalPages(response) == page
                        }
                        className="inline-flex justify-center items-center border-gray-100 bg-white border rounded text-gray-900 size-8 rtl:rotate-180"
                    >
                        <span className="sr-only">Next Page</span>
                        <ChevronRight />
                    </button>
                </li>
            </ol>
        </div>
    );
}

export default TablePaginator;
