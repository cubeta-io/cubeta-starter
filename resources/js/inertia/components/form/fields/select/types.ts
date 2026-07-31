import React, { ReactNode } from "react";

export interface Option<TValue = any, TLabel = any> {
  label: TLabel;
  value: TValue;
}

export interface IApiSelectProps<
  TResponse,
  TData,
  TMultiple extends boolean = false,
  TValue = any,
  TLabel = any,
> {
  api: (
    page?: number,
    search?: string,
    isLast?: boolean,
    totalPages?: number,
  ) => Promise<TResponse>;
  isMultiple?: TMultiple;
  optionLabel?: keyof TData;
  optionValue?: keyof TData;
  getDataArray: (response: TResponse) => TData[];
  getOptionLabel?: (item: TData) => TLabel;
  getOptionValue?: (item: TData) => TValue;
  getIsLast: (data: TResponse) => boolean;
  getTotalPages: (data: TResponse) => number;
  onSelect?: (
    selectedItem?: TData,
    selected?: Option<TValue, TLabel>[],
    setSelected?: React.Dispatch<
      React.SetStateAction<Option<TValue, TLabel>[]>
    >,
    event?: React.MouseEvent<HTMLDivElement, MouseEvent>,
  ) => void;
  defaultValue?:
    TData[] | Option<TValue, TLabel>[] | TData | Option<TValue, TLabel>;
  placeHolder?: string;
  label?: string;
  name?: string;
  closeOnSelect?: boolean;
  clearable?: boolean;
  styles?: {
    labelClasses?: string;
    selectedItemsBadgeClasses?: string;
    searchInputClasses?: string;
    loadingIcon?: () => ReactNode;
    selectedDropDownItemClasses?: string;
    dropDownItemClasses?: string;
    selectClasses?: string;
    dropDownItemsContainerClasses?: string;
    dropDownContainerMaxHeight?: number;
  };
  onChange?: TMultiple extends true
    ? (value: Option<TValue, TLabel>[]) => void
    : (value: Option<TValue, TLabel> | undefined) => void;
  revalidateOnOpen?: boolean;
  getNextPage?: (
    prevPage: number,
    isLast: boolean,
    totalPages: number,
  ) => number;
  required?: boolean;
}

export interface SelectInputProps extends Omit<
  React.ComponentProps<"input">,
  "name" | "className" | "value" | "onInput" | "ref" | "onChange"
> {}

export interface ISelectProps<TData> {
  data: TData[];
  isMultiple?: boolean;
  optionLabel?: keyof TData;
  optionValue?: keyof TData;
  getOptionLabel?: (item: TData) => TData | any;
  getOptionValue?: (item: TData) => TData | any;
  onSelect?: (
    selectedItem?: TData,
    selected?: Option[],
    setSelected?: React.Dispatch<React.SetStateAction<Option[]>>,
    event?: React.MouseEvent<HTMLDivElement, MouseEvent>,
  ) => void;
  defaultValue?: TData[] | Option[] | TData | Option;
  placeHolder?: string;
  label?: string;
  name?: string;
  closeOnSelect?: boolean;
  clearable?: boolean;
  styles?: {
    labelClasses?: string;
    selectedItemsBadgeClasses?: string;
    searchInputClasses?: string;
    loadingIcon?: () => ReactNode;
    selectedDropDownItemClasses?: string;
    dropDownItemClasses?: string;
    selectClasses?: string;
    dropDownItemsContainer?: string;
    dropDownContainerMaxHeight?: number;
  };
  onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void;
  inputProps?: SelectInputProps;
  required?: boolean;
}