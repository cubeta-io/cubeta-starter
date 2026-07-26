import { TablePaginatorProps } from "@/components/datatable/types";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { Button } from "@/components/ui/button";

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
  const totalPages = getTotalPages(response) ?? 0;
  const paginationArray = [...Array(totalPages)];

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
      setPage((old) => Math.max(old - 1, 1));
    }
  };

  const isFirstPage = () => (isFirst ? isFirst(response) : page <= 1);
  const isLastPage = () => (isLast ? isLast(response) : totalPages <= page);

  return (
    <div className="flex justify-between px-4 py-2">
      {getTotalRecords && (
        <div className="justify-start">
          Total Records : {getTotalRecords(response)}
        </div>
      )}
      <ol className="flex items-center justify-end gap-1 text-xs font-medium">
        <li>
          <Button
            type="button"
            size="icon"
            variant="secondary"
            onClick={setPrevPage}
            disabled={isFirstPage()}
            aria-label="Previous Page"
          >
            <ChevronLeft />
          </Button>
        </li>

        {paginationArray.map((_e, index) => {
          const pageNumber = index + 1;
          const isCurrentPage = pageNumber === page;

          if (index < 3 || index >= paginationArray.length - 1) {
            return (
              <li key={`page-${pageNumber}`}>
                <Button
                  type="button"
                  size="icon"
                  variant={isCurrentPage ? "default" : "outline"}
                  onClick={() => setPage(pageNumber)}
                >
                  {pageNumber}
                </Button>
              </li>
            );
          } else if (
            (index === 3 && page > 5) ||
            (index === paginationArray.length - 2 &&
              page < paginationArray.length - 4)
          ) {
            return (
              <li key={`page-${pageNumber}`}>
                <span>...</span>
              </li>
            );
          } else if (index >= page - 2 && index <= page + 1) {
            return (
              <li key={`page-${pageNumber}`}>
                <Button
                  type="button"
                  size="icon"
                  variant={isCurrentPage ? "default" : "outline"}
                  onClick={() => setPage(pageNumber)}
                >
                  {pageNumber}
                </Button>
              </li>
            );
          } else {
            return null;
          }
        })}

        <li>
          <Button
            type="button"
            size="icon"
            variant="secondary"
            onClick={setNextPage}
            disabled={isLastPage()}
            aria-label="Next Page"
          >
            <ChevronRight />
          </Button>
        </li>
      </ol>
    </div>
  );
}

export default TablePaginator;
