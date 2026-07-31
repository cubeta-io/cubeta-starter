import { ChevronDown, Loader, XIcon } from "lucide-react";
import { getNestedPropertyValue, uniqueBy } from "@/helper";
import { usePage } from "@inertiajs/react";
import React, { JSX, useCallback, useEffect, useRef, useState } from "react";
import { IApiSelectProps, Option } from "@/components/form/fields/select/types";
import {
  include,
  isEqual,
  isOption,
} from "@/components/form/fields/select/helpers";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";

function ApiSelect<
  TResponse,
  TData,
  TOptionValue extends keyof TData,
  TOptionLabel extends keyof TData = TOptionValue,
  TMultiple extends boolean = false,
>(
  props: IApiSelectProps<
    TResponse,
    TData,
    TMultiple,
    TData[TOptionValue],
    TData[TOptionLabel]
  > & {
    optionValue: TOptionValue;
    optionLabel?: TOptionLabel;
    isMultiple?: TMultiple;
    getOptionValue?: never;
    getOptionLabel?: never;
  },
): JSX.Element;

function ApiSelect<
  TResponse,
  TData,
  TOptionValue extends keyof TData,
  TLabel,
  TMultiple extends boolean = false,
>(
  props: IApiSelectProps<
    TResponse,
    TData,
    TMultiple,
    TData[TOptionValue],
    TLabel
  > & {
    optionValue: TOptionValue;
    getOptionLabel: (item: TData) => TLabel;
    isMultiple?: TMultiple;
    getOptionValue?: never;
  },
): JSX.Element;

function ApiSelect<
  TResponse,
  TData,
  TValue,
  TOptionLabel extends keyof TData,
  TMultiple extends boolean = false,
>(
  props: IApiSelectProps<
    TResponse,
    TData,
    TMultiple,
    TValue,
    TData[TOptionLabel]
  > & {
    getOptionValue: (item: TData) => TValue;
    optionLabel?: TOptionLabel;
    isMultiple?: TMultiple;
    optionValue?: never;
    getOptionLabel?: never;
  },
): JSX.Element;

function ApiSelect<
  TResponse,
  TData,
  TValue,
  TLabel,
  TMultiple extends boolean = false,
>(
  props: IApiSelectProps<TResponse, TData, TMultiple, TValue, TLabel> & {
    getOptionValue?: (item: TData) => TValue;
    getOptionLabel?: (item: TData) => TLabel;
    isMultiple?: TMultiple;
  },
): JSX.Element;

function ApiSelect<
  TResponse,
  TData,
  TMultiple extends boolean = false,
  TValue extends string | number | symbol = string | number | symbol,
  TLabel extends React.ReactNode = React.ReactNode,
>({
  api,
  getIsLast,
  getTotalPages,
  getDataArray,
  label,
  clearable = true,
  styles = undefined,
  name = undefined,
  isMultiple: isMultipleProp,
  closeOnSelect = true,
  optionLabel = undefined,
  optionValue = undefined,
  getOptionLabel = undefined,
  getOptionValue = undefined,
  onSelect = undefined,
  placeHolder = "Select An Item",
  defaultValue = undefined,
  onChange = undefined,
  revalidateOnOpen = false,
  getNextPage = undefined,
  required = false,
}: IApiSelectProps<TResponse, TData, TMultiple, TValue, TLabel>) {
  const isMultiple = isMultipleProp ?? false;
  const {
    props: { errors },
  } = usePage();
  const error = name && errors[name] ? errors[name] : undefined;

  type SelectedOption = Option<TValue, TLabel>;

  const getOption = (item: TData): SelectedOption => ({
    label: getOptionLabel
      ? getOptionLabel(item)
      : (getNestedPropertyValue(item, String(optionLabel)) ?? undefined),
    value: getOptionValue
      ? getOptionValue(item)
      : (getNestedPropertyValue(item, String(optionValue)) ?? undefined),
  });

  let df: SelectedOption[] = [];

  if (defaultValue) {
    if (!Array.isArray(defaultValue)) {
      df = [isOption(defaultValue) ? defaultValue : getOption(defaultValue)];
    } else {
      df = defaultValue.map((val) => {
        if (isOption(val)) {
          return val;
        } else return getOption(val);
      });
    }
  }

  const [isOpen, setIsOpen] = useState(false);
  const [selected, setSelected] = useState<SelectedOption[]>(df);
  const [search, setSearch] = useState<string | undefined>(undefined);
  const [items, setItems] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [page, setPage] = useState<number>(1);
  const [isLast, setIsLast] = useState(false);
  const [totalPages, setTotalPages] = useState(1);
  const fullContainer = useRef<HTMLDivElement>(null);

  const notifyChange = useCallback(
    (newSelected: SelectedOption[]) => {
      if (!onChange) {
        return;
      }
      if (isMultiple) {
        (onChange as (value: SelectedOption[]) => void)(newSelected);
      } else {
        (onChange as (value: SelectedOption | undefined) => void)(
          newSelected[0],
        );
      }
    },
    [isMultiple, onChange],
  );

  const updateSelected = useCallback(
    (newSelected: SelectedOption[]) => {
      setSelected(newSelected);
      notifyChange(newSelected);
    },
    [notifyChange],
  );

  const getData = async () => {
    if (!isLoading) {
      setIsLoading(true);
      await api(page, search, isLast, totalPages).then((data: TResponse) => {
        setItems((prev) => [...prev, ...(getDataArray(data) ?? [])]);
        setIsLoading(false);
        setIsLast(getIsLast(data) ?? true);
        setTotalPages(getTotalPages(data) ?? 1);
      });
    }
  };

  const handleClickOutside = (event: MouseEvent) => {
    if (
      fullContainer.current &&
      !fullContainer.current.contains(event.target as Node)
    ) {
      setIsOpen(false);
    }
  };

  const handleChoseItem = (
    e: React.MouseEvent<HTMLDivElement, MouseEvent>,
    item: TData,
  ) => {
    e.stopPropagation();
    onSelect?.(item, selected, setSelected, e);
    const option = getOption(item);
    let newSelected: SelectedOption[];
    if (isMultiple) {
      if (include(option, selected)) {
        newSelected = selected.filter((sel) => !isEqual(sel, option));
      } else {
        newSelected = [option, ...selected];
      }
    } else {
      if (include(option, selected)) {
        newSelected = [];
      } else {
        newSelected = [option];
      }
    }
    updateSelected(newSelected);

    if (closeOnSelect) {
      setIsOpen(false);
    }
  };

  const handleOpen = () => {
    setIsOpen((prev) => !prev);
    if (!isOpen) {
      if (revalidateOnOpen) {
        setItems([]);
        setPage(1);
        setIsLast(false);
        setTotalPages(1);
      }
      if (search) {
        setSearch(undefined);
      }
    }
  };

  const handleSearchChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setPage(1);
    setIsLast(false);
    setTotalPages(1);
    setSearch(e.target.value);
    setItems([]);
  };

  const handleClickingOnSearchInput = (
    e: React.MouseEvent<HTMLInputElement, MouseEvent>,
  ) => {
    e.stopPropagation();
    setIsOpen(true);
  };

  const handleRemoveFromSelected = (
    e: React.MouseEvent<HTMLSpanElement, MouseEvent>,
    clickedItem: SelectedOption,
  ) => {
    e.stopPropagation();
    updateSelected(selected.filter((i) => !isEqual(i, clickedItem)));
  };

  const handleDataScrolling = (e: React.UIEvent<HTMLDivElement>) => {
    const { scrollTop, clientHeight, scrollHeight } = e.currentTarget;
    const distanceFromBottom =
      scrollHeight - Math.ceil(scrollTop) - clientHeight;
    if (distanceFromBottom <= clientHeight * 0.5 && !isLoading) {
      if (getNextPage) {
        setPage((oldPage) => getNextPage(oldPage, isLast, totalPages));
      } else if (!isLast && page <= totalPages) {
        setPage((oldPage) => oldPage + 1);
      }
    }
  };

  useEffect(() => {
    if (isOpen) {
      document.addEventListener("mousedown", handleClickOutside);
      if (revalidateOnOpen) {
        getData();
      }
    }
  }, [isOpen]);

  useEffect(() => {
    getData();
  }, [page, search]);

  // Change the selected value whenever the defaultValue changes
  useEffect(() => {
    if (!defaultValue) {
      setSelected([]);
      return;
    }

    let newSelected: SelectedOption[];

    if (!Array.isArray(defaultValue)) {
      newSelected = [
        isOption(defaultValue) ? defaultValue : getOption(defaultValue),
      ];
    } else {
      newSelected = defaultValue.map((val) =>
        isOption(val) ? val : getOption(val),
      );
    }

    setSelected(newSelected);
  }, [defaultValue]);

  return (
    <Field
      className={`relative grid w-full grid-cols-1 items-center gap-3 duration-300 select-none`}
      ref={fullContainer}
    >
      {label && (
        <FieldLabel className={styles?.labelClasses}>
          {label}
          {required && <span className={"text-destructive"}>*</span>}
        </FieldLabel>
      )}

      <div
        onClick={() => handleOpen()}
        className={`dark:bg-input/30 flex cursor-pointer justify-between bg-transparent px-1.5 py-[0.470rem] transition-all duration-300 ${styles?.selectClasses ?? "text-primary w-full rounded-md border sm:text-sm"}`}
      >
        <div
          className="flex w-full items-center justify-between"
          role={"listbox"}
          aria-expanded={isOpen}
          aria-activedescendant={
            selected.length ? String(selected[0].value) : undefined
          }
        >
          {selected.length > 0 ? (
            <div className="flex flex-wrap items-center gap-1">
              {selected.map((option, index) => (
                <div className="flex flex-wrap gap-1" key={index}>
                  <Badge onClick={(e) => handleRemoveFromSelected(e, option)}>
                    {option.label}
                  </Badge>
                </div>
              ))}
            </div>
          ) : (
            <p className={placeHolder ?? "transition-opacity duration-300"}>
              {placeHolder ?? `Select ${label} ...`}
            </p>
          )}
          <div className="flex items-center gap-2">
            {isLoading && (
              <div className="">
                {styles?.loadingIcon ? (
                  styles.loadingIcon()
                ) : (
                  <Loader className="text-primary h-full w-full animate-spin" />
                )}
              </div>
            )}
            {selected.length > 0 && clearable && (
              <XIcon
                className="text-primary h-5 w-5 transition-transform duration-300 hover:scale-110"
                onClick={(e) => {
                  e.stopPropagation();
                  updateSelected([]);
                }}
              />
            )}
            <ChevronDown
              className={`text-primary h-5 w-5 font-extrabold transition-transform duration-300 ${isOpen && "rotate-180"}`}
            />
          </div>
        </div>
        <div
          className={`absolute left-0 z-50 overflow-y-scroll transition-all duration-300 ${
            isOpen
              ? "scale-100 opacity-100"
              : "pointer-events-none scale-95 opacity-0"
          } ${styles?.dropDownItemsContainerClasses ?? "bg-popover w-full rounded-lg border px-3 pb-3 shadow-2xl"}`}
          style={{
            top: `${(fullContainer?.current?.clientHeight ?? 0) + 5}px`,
            maxHeight: `${styles?.dropDownContainerMaxHeight ?? "200"}px`,
            overflowY: "scroll",
          }}
          onScroll={(e) => handleDataScrolling(e)}
        >
          <div className={`sticky top-1 bg-inherit`}>
            <Input
              className={`${styles?.searchInputClasses ?? " "} text-primary my-2 w-full p-1 transition-shadow duration-300`}
              onClick={(e) => handleClickingOnSearchInput(e)}
              onChange={(e) => handleSearchChange(e)}
              value={search ?? ""}
              name={"search-box"}
              type={"search"}
              placeholder={"Search ..."}
            />
          </div>

          {uniqueBy(
            items,
            optionValue ?? getOption(items?.[0] ?? "id").value,
          ).map((item, index) => (
            <div
              key={index}
              className={`transition-colors duration-300 ${
                include(getOption(item), selected)
                  ? `${styles?.selectedDropDownItemClasses ?? "bg-foreground text-secondary"}`
                  : `${styles?.dropDownItemClasses ?? "hover:bg-foreground text-primary hover:text-secondary my-1 w-full cursor-pointer rounded-md p-2"}`
              } ${styles?.dropDownItemClasses ?? "hover:bg-foreground text-primary hover:text-secondary my-1 w-full cursor-pointer rounded-md p-2"}`}
              onClick={(e) => handleChoseItem(e, item)}
            >
              {getOption(item).label ?? ""}
            </div>
          ))}
          {isLoading && (
            <div className="text-primary my-2 flex w-full items-center justify-center transition-opacity duration-300">
              Loading ...
            </div>
          )}
        </div>
      </div>
      {error && <FieldError>{error}</FieldError>}
      {errors &&
        isMultiple &&
        selected.length > 0 &&
        name &&
        Object.entries(errors).map(([key, value], index) => {
          if (key.startsWith(name)) {
            return <FieldError key={index}>{value}</FieldError>;
          }
        })}
    </Field>
  );
}
export default ApiSelect;
