import { ChevronDown, Loader, XIcon } from "lucide-react";
import { getNestedPropertyValue, uniqueBy } from "@/helper";
import { usePage } from "@inertiajs/react";
import React, { ChangeEvent, useEffect, useRef, useState } from "react";
import { IApiSelectProps, Option } from "@/components/form/fields/select/types";
import { isEqual, isOption } from "@/components/form/fields/select/helpers";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import { Badge } from "@/components/ui/badge";
import { Input } from "@/components/ui/input";

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
  const {
    props: { errors },
  } = usePage();
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

  useEffect(() => {
    inputRef?.current?.dispatchEvent(new Event("input", { bubbles: true }));
  }, [selected]);

  const getInputValue = () => {
    if (isMultiple) {
      return JSON.stringify(selected.map((option) => option.value));
    } else {
      return selected?.[0]?.value ?? "";
    }
  };

  // Change the selected value whenever the defaultValue changes
  useEffect(() => {
    if (!defaultValue) {
      setSelected([]);
      return;
    }

    let newSelected: Option[];

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
        <FieldLabel htmlFor={`${name}_id`} className={styles?.labelClasses}>
          {label}
          {required && <span className={"text-destructive"}>*</span>}
        </FieldLabel>
      )}

      <input
        ref={inputRef}
        id={`${name}_id`}
        name={name}
        value={getInputValue()}
        className={`hidden`}
        onChange={(e) => {
          if (!onChange) {
            return;
          }
          let arrayValues: [];
          try {
            arrayValues = e.target.value ? JSON.parse(e.target.value) : [];
          } catch (error) {
            arrayValues = [];
            console.log("Target Value:", e.target.value);
            console.error("Error in Api Multi select");
            console.error(error);
            console.error(`Error caused by this value: ${e.target.value}`);
          }
          onChange(e as ChangeEvent<HTMLInputElement>, arrayValues);
        }}
        {...inputProps}
      />

      <div
        onClick={() => handleOpen()}
        className={`dark:bg-input/30 flex cursor-pointer justify-between bg-transparent px-1.5 py-[0.470rem] transition-all duration-300 ${styles?.selectClasses ?? "text-primary w-full rounded-md border sm:text-sm"}`}
      >
        <div
          className="flex w-full items-center justify-between"
          role={"listbox"}
          aria-expanded={isOpen}
          aria-activedescendant={
            selected.length ? selected[0].value : undefined
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
                  setSelected([]);
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

const include = (option: Option, selected: Option[]): boolean =>
  selected.filter((op) => isEqual(op, option)).length > 0;

export default ApiSelect;
