import { useEffect, useState } from "react";
import LoadingSpinner from "@/Components/icons/LoadingSpinner";
import TableHead from "@/Components/Datatable/TableHead";
import TableBody from "@/Components/Datatable/TableBody";
import PageCard from "@/Components/ui/PageCard";
import { DataTableData } from "@/Components/Datatable/DataTableUtils";
import TableActions from "@/Components/Datatable/TableActions";
import TablePaginator from "@/Components/Datatable/TablePaginator";
import Modal from "@/Components/ui/Modal";
import ImportModal from "@/Components/Datatable/ImportModal";
import ExportModal from "@/Components/Datatable/ExportModal";

function DataTable<ApiResponse, Data>({
  api,
  schema,
  createUrl,
  filter,
  title,
  getDataArray,
  getTotalPages,
  getNextPage = undefined,
  getPreviousPage = undefined,
  getTotalRecords = undefined,
  isFirst = undefined,
  isLast = undefined,
  importExampleRoute = undefined,
  importRoute = undefined,
  exportRoute = undefined,
  exportables = undefined,
}: DataTableData<ApiResponse, Data>) {
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState("");
  const [hideCols, setHideCols] = useState<number[]>([]);
  const [perPage, setPerPage] = useState(10);
  const [params, setParams] = useState({});
  const [tempParams, setTempParams] = useState({});
  const [openFilter, setOpenFilter] = useState(false);
  const [sortDir, setSortDir] = useState("asc");
  const [sortCol, setSortCol] = useState("");
  const [refetch, setRefetch] = useState(false);
  const [items, setItems] = useState<Data[]>([]);
  const [isPending, setIsPending] = useState(false);
  const [response, setApiResponse] = useState<ApiResponse>();
  const [openExport, setOpenExport] = useState(false);
  const [openImport, setOpenImport] = useState(false);

  const revalidate = () => {
    setRefetch((prevState) => !prevState);
  };

  const fetchFromApi = async () => {
    setIsPending(true);

    let s = !search || search == "" ? undefined : search;
    let sortD = !sortDir || sortDir == "" ? undefined : sortDir;
    let sortC = !sortCol || sortCol == "" ? undefined : sortCol;

    const res = await api(page, s, sortC, sortD, perPage, params);
    setApiResponse(res);
    setItems(getDataArray(res) ?? []);
    setIsPending(false);
  };

  useEffect(() => {
    fetchFromApi();
  }, [page, search, sortDir, sortCol, perPage, params, refetch]);

  return (
    <>
      <div className={`relative`}>
        {isPending ? (
          <div className="absolute left-1/2 top-1/2 z-10 m-auto flex h-full w-full -translate-x-1/2 -translate-y-1/2 transform items-center justify-center bg-transparent/5 text-center opacity-70">
            <LoadingSpinner className="h-8 w-8 dark:text-white" />
          </div>
        ) : null}
        <Modal
          isOpen={openFilter}
          onClose={() => {
            setOpenFilter(false);
          }}
        >
          {filter ? filter(tempParams, setTempParams) : null}
          <div className="mt-4 flex items-center justify-between">
            <button
              type="button"
              className="bg-info inline-flex justify-center rounded-md px-4 py-2 text-sm font-medium text-blue-900 hover:bg-blue-200 focus:outline-none"
              onClick={() => {
                setParams(tempParams);
                setOpenFilter(false);
              }}
            >
              Apply
            </button>

            <button
              type="button"
              className="bg-danger inline-flex justify-center rounded-md px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none"
              onClick={() => {
                setTempParams({});
                setParams({});
                setOpenFilter(false);
              }}
            >
              Reset Filters
            </button>
          </div>
        </Modal>
        {importRoute && (
          <ImportModal
            openImport={openImport}
            setOpenImport={setOpenImport}
            revalidate={revalidate}
            importRoute={importRoute}
            importExampleRoute={importExampleRoute}
          />
        )}
        {exportRoute && (
          <ExportModal
            openExport={openExport}
            schema={schema}
            setOpenExport={setOpenExport}
            exportRoute={exportRoute}
            exportables={exportables}
          />
        )}
        <PageCard>
          <div>
            {title ? (
              <h1 className="text-xl font-bold dark:text-white">{title}</h1>
            ) : (
              ""
            )}
            <TableActions
              perPage={perPage}
              setPerPage={setPerPage}
              setPage={setPage}
              setOpenFilter={setOpenFilter}
              setSearch={setSearch}
              search={search}
              filter={filter}
              createUrl={createUrl}
              setOpenExport={setOpenExport}
              setOpenImport={setOpenImport}
              importable={importRoute != undefined}
              exportable={exportRoute != undefined}
            />
            <div className="rounded-lg">
              <div className="overflow-x-auto rounded-t-lg">
                <table className="bg-white-secondary dark:bg-dark-secondary relative min-w-full scroll-my-0 overflow-y-hidden text-sm dark:text-white">
                  <TableHead
                    schema={schema}
                    setSortDir={setSortDir}
                    setSortCol={setSortCol}
                    sortDir={sortDir}
                    sortCol={sortCol}
                  />

                  <TableBody
                    data={items}
                    tableSchema={schema}
                    hidden={hideCols}
                    setHidden={setHideCols}
                    revalidate={revalidate}
                  />
                </table>
              </div>

              <TablePaginator
                key={"next-page"}
                response={response ?? ({} as ApiResponse)}
                page={page}
                setPage={setPage}
                getTotalPages={getTotalPages}
                getNextPage={getNextPage}
                getPreviousPage={getPreviousPage}
                getTotalRecords={getTotalRecords}
                isFirst={isFirst}
                isLast={isLast}
              />
            </div>
          </div>
        </PageCard>
      </div>
    </>
  );
}

export default DataTable;
