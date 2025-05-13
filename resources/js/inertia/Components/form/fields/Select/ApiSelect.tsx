import {
  IApiSelectProps,
  isEqual,
  isOption,
  Option,
} from "@/Components/form/fields/Select/SelectUtils";
import ChevronDown from "@/Components/icons/ChevronDown";
import LoadingSpinner from "@/Components/icons/LoadingSpinner";
import XMark from "@/Components/icons/XMark";
import { getNestedPropertyValue } from "@/helper";
import { usePage } from "@inertiajs/react";
import React, { ChangeEvent, useEffect, useRef, useState } from "react";

function ApiSelect<TResponse, TData>({
  api,
  getIsLast,
  getTotalPages,
  getDataArray,
  label,
  clearable = true,
  styles = undefined,
  name = undefined,
  isMultiple = false,
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
  inputProps = {},
  getNextPage = undefined,
  required = false,
}: IApiSelectProps<TResponse, TData>) {
  const errors = usePage().props.errors;
  const error = name && errors[name] ? errors[name] : undefined;

  const getOption = (item: TData): Option => ({
    label: getOptionLabel
      ? getOptionLabel(item)
      : (getNestedPropertyValue(item, String(optionLabel)) ?? undefined),
    value: getOptionValue
      ? getOptionValue(item)
      : (getNestedPropertyValue(item, String(optionValue)) ?? undefined),
  });

  let df: Option[] = [];

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
  const [selected, setSelected] = useState<{ label: any; value: any }[]>(df);
  const [search, setSearch] = useState<string | undefined>(undefined);
  const [items, setItems] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [page, setPage] = useState<number>(1);
  const [isLast, setIsLast] = useState(false);
  const [totalPages, setTotalPages] = useState(1);
  const inputRef = useRef<HTMLInputElement>(null);
  const fullContainer = useRef<HTMLDivElement>(null);

  const getData = async () => {
    if (!isLoading) {
      setIsLoading(true);
      await api(page, search, isLast, totalPages).then((data: TResponse) => {
        setItems((prev) => [...(getDataArray(data) ?? []), ...prev]);
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
    if (onSelect) {
      onSelect(item, selected, setSelected, e);
    } else {
      const option = getOption(item);
      if (isMultiple) {
        if (include(option, selected)) {
          setSelected((prev) => prev.filter((sel) => !isEqual(sel, option)));
        } else {
          setSelected((prev) => [option, ...prev]);
        }
      } else {
        if (include(option, selected)) {
          setSelected([]);
        } else {
          setSelected([option]);
        }
      }
    }

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
    clickedItem: Option,
  ) => {
    e.stopPropagation();
    setSelected((prev) => prev.filter((i) => !isEqual(i, clickedItem)));
  };

  const handleDataScrolling = (e: any) => {
    const { scrollTop, clientHeight, scrollHeight } = e.target;
    if (scrollHeight - scrollTop === clientHeight) {
      if (getNextPage) {
        setPage((oldPage) => getNextPage(oldPage, isLast, totalPages));
      }
      if (!isLast && page <= totalPages) {
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

  useEffect(() => {
    inputRef?.current?.dispatchEvent(new Event("input", { bubbles: true }));
  }, [selected]);

  const getInputValue = () => {
    if (isMultiple) {
      return `[${selected.map((option) => option.value)}]`;
    } else {
      return selected?.[0]?.value ?? "";
    }
  };

  return (
    <div className="relative w-full select-none" ref={fullContainer}>
      <label
        className={`block ${
          styles?.labelClasses ??
          "select-text text-sm font-medium text-gray-900 dark:text-white"
        }`}
      >
        {label ?? ""}
        {required ? <span className="text-sm text-red-500">*</span> : ""}
        <input
          ref={inputRef}
          name={name ?? ""}
          value={getInputValue()}
          className={`hidden`}
          onInput={(e) => {
            if (onChange) {
              onChange(e as ChangeEvent<HTMLInputElement>);
            }
          }}
          {...inputProps}
        />
      </label>

      <div
        onClick={() => handleOpen()}
        className={`flex cursor-pointer justify-between ${
          styles?.selectClasses ??
          "w-full rounded-lg border border-gray-300 p-2 text-gray-700 sm:text-sm"
        }`}
      >
        <div className="flex w-full items-center justify-between">
          {selected.length > 0 ? (
            <div className="flex flex-wrap items-center gap-1">
              {selected.map((option, index) => (
                <div className="flex flex-wrap gap-1" key={index}>
                  <span
                    className={`${
                      styles?.selectedItemsBadgeClasses ??
                      "rounded-sm bg-gray-500 p-0.5 text-white hover:bg-red-400"
                    } cursor-pointer`}
                    onClick={(e) => handleRemoveFromSelected(e, option)}
                  >
                    {option.label}
                  </span>
                </div>
              ))}
            </div>
          ) : (
            <p className={"dark:text-white"}>{placeHolder}</p>
          )}
          <div className="flex items-center gap-2">
            {isLoading && (
              <div className="">
                {styles?.loadingIcon ? (
                  styles.loadingIcon()
                ) : (
                  <LoadingSpinner className="text-primary h-full w-full" />
                )}
              </div>
            )}
            {selected.length > 0 && clearable ? (
              <XMark
                onClick={(e) => {
                  e.stopPropagation();
                  setSelected([]);
                }}
              />
            ) : (
              ""
            )}
            <ChevronDown />
          </div>
        </div>
        <div
          className={
            isOpen
              ? `absolute left-0 z-50 ${
                  styles?.dropDownItemsContainerClasses ??
                  "bg-white-secondary dark:bg-dark-secondary w-full rounded-lg border border-gray-200 px-3 pb-3 shadow-2xl"
                }`
              : "hidden"
          }
          style={{
            top: `${(fullContainer?.current?.clientHeight ?? 0) + 5}px`,
            maxHeight: `${styles?.dropDownContainerMaxHeight ?? "200"}px`,
            overflowY: "scroll",
          }}
          onScroll={(e) => handleDataScrolling(e)}
        >
          <div className={`sticky top-0 bg-inherit`}>
            <input
              className={`${
                styles?.searchInputClasses ??
                "focus:border-primary focus:outline-primary dark:bg-secondary my-2 w-full rounded-md p-1 placeholder-white dark:text-white"
              }`}
              onClick={(e) => handleClickingOnSearchInput(e)}
              onChange={(e) => handleSearchChange(e)}
              value={search ?? ""}
              name={"search-box"}
              type={"text"}
              placeholder={"Search ..."}
            />
          </div>

          {items.map((item, index) => (
            <div
              key={index}
              className={` ${
                include(getOption(item), selected)
                  ? `${
                      styles?.selectedDropDownItemClasses ??
                      "bg-primary border-primary"
                    }`
                  : ""
              } ${
                styles?.dropDownItemClasses ??
                "hover:border-primary hover:bg-primary my-1 w-full cursor-pointer rounded-md p-2 text-black dark:text-white"
              }`}
              onClick={(e) => handleChoseItem(e, item)}
            >
              {getOption(item).label ?? ""}
            </div>
          ))}

          {isLoading && (
            <div className="my-2 flex w-full items-center justify-center dark:text-white">
              Loading ...
            </div>
          )}
        </div>
      </div>
      {error ? <p className={"text-sm text-red-700"}>{error}</p> : ""}
    </div>
  );
}

const include = (option: Option, selected: Option[]): boolean =>
  selected.filter((op) => isEqual(op, option)).length > 0;

export default ApiSelect;
