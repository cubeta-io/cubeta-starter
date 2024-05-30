"use client";
import { useEffect, useState } from "react";
import LoadingSpinner from "../icons/LoadingSpinner";
import TableHead from "./TableHead";
import TableBody from "./TableBody";
import PageCard from "../ui/PageCard";
import { DataTableData } from "./DataTableUtils";
import TableActions from "./TableActions";
import TablePaginator from "./TablePaginator";
import Modal from "../ui/Modal";
import ImportModal from "./ImportModal";
import ExportModal from "./ExportModal";

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
        setItems(getDataArray(res));
        setIsPending(false);
    };

    useEffect(() => {
        fetchFromApi();
    }, [page, search, sortDir, sortCol, perPage, params, refetch]);

    return (
        <>
            <div className={`relative`}>
                {isPending ? (
                    <div className="top-1/2 left-1/2 z-10 absolute flex justify-center items-center bg-transparent/5 opacity-70 m-auto w-full h-full text-center transform -translate-x-1/2 -translate-y-1/2">
                        <LoadingSpinner className="w-8 h-8" />
                    </div>
                ) : null}
                <Modal
                    isOpen={openFilter}
                    onClose={() => {
                        setOpenFilter(false);
                    }}
                >
                    {filter ? filter(tempParams, setTempParams) : null}
                    <div className="flex justify-between items-center mt-4">
                        <button
                            type="button"
                            className="inline-flex justify-center bg-blue-100 hover:bg-blue-200 px-4 py-2 border border-transparent rounded-md font-medium text-blue-900 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                            onClick={() => {
                                setParams(tempParams);
                                setOpenFilter(false);
                            }}
                        >
                            Apply
                        </button>

                        <button
                            type="button"
                            className="inline-flex justify-center bg-danger hover:bg-red-700 px-4 py-2 border border-transparent rounded-md font-medium text-sm text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2"
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
                            <h1 className="font-bold text-xl">{title}</h1>
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
                        <div className="border-gray-200 border rounded-lg">
                            <div className="rounded-t-lg overflow-x-auto">
                                <table className="relative bg-white scroll-my-0 divide-y-2 divide-gray-200 min-w-full text-sm overflow-y-hidden">
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
