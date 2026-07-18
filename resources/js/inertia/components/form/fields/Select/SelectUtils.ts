import React, { ReactNode } from "react";

export interface Option {
  label: any;
  value: any;
}

export interface SelectInputProps
  extends Omit<
    React.ComponentProps<"input">,
    "name" | "className" | "value" | "onInput" | "ref" | "onChange"
  > {}

export interface IApiSelectProps<TResponse, TData> {
  api: (
    page?: number,
    search?: string,
    isLast?: boolean,
    totalPages?: number,
  ) => Promise<TResponse>;
  isMultiple?: boolean;
  optionLabel?: keyof TData;
  optionValue?: keyof TData;
  getDataArray: (response: TResponse) => TData[];
  getOptionLabel?: (item: TData) => TData | any;
  getOptionValue?: (item: TData) => TData | any;
  getIsLast: (data: TResponse) => boolean;
  getTotalPages: (data: TResponse) => number;
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
    dropDownItemsContainerClasses?: string;
    dropDownContainerMaxHeight?: number;
  };
  onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void;
  inputProps?: SelectInputProps;
  revalidateOnOpen?: boolean;
  getNextPage?: (
    prevPage: number,
    isLast: boolean,
    totalPages: number,
  ) => number;
  required?: boolean;
}

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

export const isEqual = (option1: Option, option2: Option): boolean =>
  (option1.label ?? undefined) == (option2.label ?? undefined) &&
  (option1.value ?? undefined) == (option2.value ?? undefined);

export const include = (option: Option, selected: Option[]): boolean =>
  selected.filter((op) => isEqual(op, option)).length > 0;

export const isOption = (object: any): object is Option =>
  "label" in object && "value" in object;
