import { ReactNode, ThHTMLAttributes } from "react";

export interface FilterParam {
  [key: string]: any;
}

export interface DataTableSchema<Data> {
  name?: keyof Data | string;
  label: string;
  sortable?: boolean;
  translatable?: boolean;
  headerProps?: ThHTMLAttributes<HTMLTableHeaderCellElement> | undefined | null;
  cellProps?: ThHTMLAttributes<HTMLTableHeaderCellElement> | undefined | null;
  hidden?: number[];
  render?: (
    data: any,
    fullObject?: Data,
    setHidden?: (value: ((prevState: number[]) => number[]) | number[]) => void,
    revalidate?: () => void,
  ) => ReactNode | React.JSX.Element | undefined | null;
}

export interface DataTableData<ApiResponse, Data> {
  title?: string;
  createUrl?: string;
  importRoute?: string;
  importExampleRoute?: string;
  exportRoute?: string;
  exportables?: string[];
  getDataArray: (res: ApiResponse) => Data[]|undefined;
  getTotalPages: (res: ApiResponse) => number;
  getNextPage?: (res: ApiResponse) => number;
  getPreviousPage?: (res: ApiResponse) => number;
  getTotalRecords?: (res: ApiResponse) => number;
  isFirst?: (response: ApiResponse) => boolean;
  isLast?: (response: ApiResponse) => boolean;
  api: (
    page?: number,
    search?: string,
    sortCol?: string,
    sortDir?: string,
    perPage?: number,
    params?: object,
  ) => Promise<ApiResponse>;
  schema: DataTableSchema<Data>[];
  filter?: (
    params: FilterParam,
    setParams: (
      value: ((prevState: FilterParam) => FilterParam) | FilterParam,
    ) => void,
  ) => ReactNode | React.JSX.Element | undefined | null;
}

export interface TableActionsProps {
  createUrl?: string;
  search: string;
  perPage: number;
  importable: boolean;
  exportable: boolean;
  setPerPage: (value: number | ((prev: number) => number)) => void;
  setPage: (value: number | ((prev: number) => number)) => void;
  setOpenFilter: (value: boolean | ((prev: boolean) => boolean)) => void;
  setSearch: (value: string | ((prev: string) => string)) => void;
  setOpenImport: (value: boolean | ((prev: boolean) => boolean)) => void;
  setOpenExport: (value: boolean | ((prev: boolean) => boolean)) => void;
  filter?: (
    params: FilterParam,
    setParams: (
      value: ((prevState: FilterParam) => FilterParam) | FilterParam,
    ) => void,
  ) => ReactNode | React.JSX.Element | undefined | null;
}

export interface TableBodyProps<Data> {
  tableSchema: DataTableSchema<Data>[];
  data: Data[];
  setHidden: (value: ((prevState: number[]) => number[]) | number[]) => void;
  revalidate?: () => void;
  hidden: number[];
}

export interface TableHeadProps<Data> {
  schema: DataTableSchema<Data>[];
  sortDir: string;
  setSortDir: (value: ((prevState: string) => string) | string) => void;
  sortCol: string;
  setSortCol: (value: ((prevState: string) => string) | string) => void;
}

export interface TablePaginatorProps<ApiResponse> {
  response: ApiResponse;
  page: number;
  setPage: (value: ((prevState: number) => number) | number) => void;
  getTotalPages: (res: ApiResponse) => number;
  getNextPage?: (res: ApiResponse, prevPageNumber?: number) => number;
  getPreviousPage?: (res: ApiResponse, prevPageNumber?: number) => number;
  getTotalRecords?: (res: ApiResponse) => number;
  isFirst?: (response: ApiResponse) => boolean;
  isLast?: (response: ApiResponse) => boolean;
}
