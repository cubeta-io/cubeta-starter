import ChevronDown from "@/Components/icons/ChevronDown";
import XMark from "@/Components/icons/XMark";
import { getNestedPropertyValue } from "@/helper";
import React, { useEffect, useRef, useState } from "react";
import {
  include,
  ISelectProps,
  isEqual,
  isOption,
  Option,
} from "@/Components/form/fields/Select/SelectUtils";
import { usePage } from "@inertiajs/react";

function Select<TData>({
  label,
  data = [],
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
  required = false,
  inputProps = {},
}: ISelectProps<TData>) {
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
  const inputRef = useRef<HTMLInputElement>(null);
  const fullContainer = useRef<HTMLDivElement>(null);

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
      setSearch(undefined);
    }
  };

  const handleSearchChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setSearch(e.target.value);
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

  useEffect(() => {
    inputRef?.current?.dispatchEvent(new Event("input", { bubbles: true }));
    if (isOpen) {
      document.addEventListener("mousedown", handleClickOutside);
    }
  }, [selected, isOpen]);

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
          "select-text text-sm font-medium text-gray-900"
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
              onChange(e as React.ChangeEvent<HTMLInputElement>);
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
            <p>{placeHolder}</p>
          )}
          <div className="flex items-center gap-2">
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
                  styles?.dropDownItemsContainer ??
                  "dark:bg-dark w-full rounded-lg border border-gray-200 bg-white px-3 pb-3 shadow-2xl"
                }`
              : "hidden"
          }
          style={{
            top: `${(fullContainer?.current?.clientHeight ?? 0) + 5}px`,
            maxHeight: `${styles?.dropDownContainerMaxHeight ?? "300"}px`,
            overflowY: "scroll",
          }}
        >
          <div className={`sticky top-0 bg-inherit`}>
            <input
              className={`${
                styles?.searchInputClasses ??
                "focus:border-primary focus:outline-primary my-2 w-full rounded-md p-1"
              }`}
              onClick={(e) => handleClickingOnSearchInput(e)}
              onChange={(e) => handleSearchChange(e)}
              value={search ?? ""}
              name={"search-box"}
              type={"text"}
              placeholder={"Search ..."}
            />
          </div>

          {data.map((item, index) => {
            if (!search) {
              return (
                <div
                  key={index}
                  className={`${
                    include(getOption(item), selected)
                      ? `${
                          styles?.selectedDropDownItemClasses ??
                          "bg-primary border-primary"
                        }`
                      : ""
                  } ${
                    styles?.dropDownItemClasses ??
                    "hover:border-primary hover:bg-primary my-1 w-full cursor-pointer rounded-md p-2 text-black"
                  }`}
                  onClick={(e) => handleChoseItem(e, item)}
                >
                  {getOption(item).label ?? ""}
                </div>
              );
            } else {
              const escapedQuery = search.replace(
                /[.*+?^${}()|[\]\\]/g,
                "\\$&",
              );
              const regex = new RegExp(escapedQuery, "i");
              if (regex.test(getOption(item).label ?? "")) {
                return (
                  <div
                    key={index}
                    className={`${
                      include(getOption(item), selected)
                        ? `${
                            styles?.selectedDropDownItemClasses ??
                            "bg-primary border-primary"
                          }`
                        : ""
                    } ${
                      styles?.dropDownItemClasses ??
                      "hover:border-primary hover:bg-primary my-1 w-full cursor-pointer rounded-md p-2 text-black"
                    }`}
                    onClick={(e) => handleChoseItem(e, item)}
                  >
                    {getOption(item).label ?? ""}
                  </div>
                );
              } else {
                return "";
              }
            }
          })}
        </div>
      </div>
      {error ? <p className={"text-sm text-red-700"}>{error}</p> : ""}
    </div>
  );
}

export default Select;
