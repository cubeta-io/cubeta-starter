import { ReactNode, ThHTMLAttributes } from "react";

export interface FilterParam {
  [key: string]: any;
}

export interface TableSchema<Data> {
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

export interface TableData<ApiResponse, Data> {
  title?: string;
  createUrl?: string;
  importRoute?: string;
  importExampleRoute?: string;
  exportRoute?: string;
  exportables?: string[];
  getDataArray: (res: ApiResponse) => Data[] | undefined;
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
  schema: TableSchema<Data>[];
  filter?: (
    params: FilterParam,
    setParams: (key: string, value: Json) => void,
  ) => ReactNode | React.JSX.Element | undefined | null;
}

export interface TableBodyProps<Data> {
  tableSchema: TableSchema<Data>[];
  data: Data[];
  setHidden: (value: ((prevState: number[]) => number[]) | number[]) => void;
  revalidate?: () => void;
  hidden: number[];
}

export interface TableHeadProps<Data> {
  schema: TableSchema<Data>[];
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

export type Json =
  | string
  | number
  | boolean
  | null
  | { [key: string]: Json | undefined }
  | Json[];
