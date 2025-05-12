import { TablePaginatorProps } from "@/Components/Datatable/DataTableUtils";
import ChevronRight from "@/Components/icons/ChevronRight";
import ChevronLeft from "@/Components/icons/ChevronLeft";

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

  const isFirstPage = () => {
    return isFirst ? isFirst(response) : page <= 1;
  };

  const isLastPage = () => {
    return isLast ? isLast(response) : getTotalPages(response) <= page;
  };

  return (
    <div className="flex justify-between px-4 py-2">
      {getTotalRecords && (
        <div className={"justify-start dark:text-white"}>
          Total Records : {getTotalRecords(response)}
        </div>
      )}
      <ol className="flex items-center justify-end gap-1 text-xs font-medium">
        <li className={`border-0`}>
          <button
            onClick={() => setPrevPage()}
            disabled={isFirstPage()}
            className="bg-secondary inline-flex size-8 cursor-pointer items-center justify-center rounded-md border-0 text-white outline-0 rtl:rotate-180"
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
                  className={`size-8 cursor-pointer rounded-md text-center leading-8 ${
                    index + 1 == page
                      ? "bg-primary text-white"
                      : "text-primary bg-white dark:bg-white"
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
                  className={`size-8 cursor-pointer rounded-md text-center leading-8 ${
                    index + 1 == page
                      ? "bg-primary text-white"
                      : "text-primary bg-white dark:bg-white"
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
            disabled={isLastPage()}
            className="bg-secondary inline-flex size-8 cursor-pointer items-center justify-center rounded-md border-0 text-white outline-0 rtl:rotate-180"
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
