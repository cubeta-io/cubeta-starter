import ExportModal from "@/components/datatable/export-modal";
import FilterModal from "@/components/datatable/filter-modal";
import ImportModal from "@/components/datatable/import-modal";
import TableBody from "@/components/datatable/table-body";
import TableHead from "@/components/datatable/table-head";
import TablePaginator from "@/components/datatable/table-paginator";
import PageCard from "@/components/ui/page-card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Table,
  TableBody as ShadcnTableBody,
  TableCell,
  TableRow,
} from "@/components/ui/table";
import { Link } from "@inertiajs/react";
import { useEffect, useState } from "react";
import { Json, TableData } from "@/components/datatable/types";
import { Plus } from "lucide-react";

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
}: TableData<ApiResponse, Data>) {
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
  }, [page, search, sortDir, sortCol, perPage, params, refetch, api]);

  const setFilter = (key: string, value: Json) => {
    if (value === undefined || value === null) {
      setTempParams((prev) => {
        const { [key as keyof typeof tempParams]: neglected, ...rest } = prev;
        return rest;
      });
    } else {
      setTempParams((prevState) => ({
        ...prevState,
        [key]: value,
      }));
    }
  };

  return (
    <PageCard title={title}>
      <div className={`my-2 flex w-full items-center justify-between`}>
        <div className={"flex gap-1"}>
          {createUrl && (
            <Link href={createUrl}>
              <Button size={"icon"} type={"button"} variant={"default"}>
                <Plus />
              </Button>
            </Link>
          )}
          {filter && (
            <FilterModal
              open={openFilter}
              onOpenChange={setOpenFilter}
              onReset={() => {
                setTempParams({});
                setParams({});
                setOpenFilter(false);
              }}
              onApply={() => {
                setParams(tempParams);
                setOpenFilter(false);
              }}
            >
              {filter(tempParams, setFilter)}
            </FilterModal>
          )}
          {importRoute && (
            <ImportModal
              revalidate={revalidate}
              importRoute={importRoute}
              importExampleRoute={importExampleRoute}
            />
          )}
          {exportRoute && (
            <ExportModal
              exportables={exportables}
              exportRoute={exportRoute}
              schema={schema}
            />
          )}
        </div>
        <div className={"flex gap-2"}>
          <Select
            value={perPage.toString()}
            onValueChange={(value) => {
              if (value) {
                setPage(1);
                setSearch("");
                setPerPage(parseInt(value));
              }
            }}
          >
            <SelectTrigger className="w-20">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {["10", "25", "50", "75", "500"].map((size) => (
                <SelectItem key={size} value={size}>
                  {size}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>

          <Input
            type="search"
            id="Search"
            placeholder="Search For ..."
            value={search}
            onChange={(e) => {
              setSearch(e.target.value);
              setPage(1);
            }}
          />
        </div>
      </div>

      <Table className="relative min-w-full scroll-my-0 overflow-x-scroll overflow-y-hidden text-sm">
        <TableHead
          schema={schema}
          setSortDir={setSortDir}
          setSortCol={setSortCol}
          sortDir={sortDir}
          sortCol={sortCol}
        />
        {isPending ? (
          <ShadcnTableBody>
            <TableRow>
              <TableCell colSpan={schema.length} className={"p-3 text-center"}>
                Loading ...
              </TableCell>
            </TableRow>
          </ShadcnTableBody>
        ) : (
          <TableBody
            data={items}
            tableSchema={schema}
            hidden={hideCols}
            setHidden={setHideCols}
            revalidate={revalidate}
          />
        )}
      </Table>

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
    </PageCard>
  );
}

export default DataTable;
