import ChevronDown from "@/Components/icons/ChevronDown";
import LoadingSpinner from "@/Components/icons/LoadingSpinner";
import XMark from "@/Components/icons/XMark";
import { getNestedPropertyValue } from "@/helper";
import React, { ChangeEvent, useEffect, useRef, useState } from "react";
import {
    IApiSelectProps,
    isEqual,
    isOption,
    Option,
} from "@/Components/form/fields/Select/SelectUtils";
import { usePage } from "@inertiajs/react";

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
                                         defaultValues = undefined,
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
            : getNestedPropertyValue(item, String(optionLabel)) ?? undefined,
        value: getOptionValue
            ? getOptionValue(item)
            : getNestedPropertyValue(item, String(optionValue)) ?? undefined,
    });

    let df: Option[] = [];

    if (defaultValues) {
        df = defaultValues.map((val) => {
            if (isOption(val)) {
                return val;
            } else return getOption(val);
        });
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
            await api(page, search, isLast, totalPages).then(
                (data: TResponse) => {
                    setItems((prev) => [
                        ...(getDataArray(data) ?? []),
                        ...prev,
                    ]);
                    setIsLoading(false);
                    setIsLast(getIsLast(data) ?? true);
                    setTotalPages(getTotalPages(data) ?? 1);
                },
            );
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
                    setSelected((prev) =>
                        prev.filter((sel) => !isEqual(sel, option)),
                    );
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
                    "font-medium text-gray-900 dark:text-white text-sm select-text"
                }`}
            >
                {label ?? ""}
                {required ? (
                    <span className="text-red-500 text-sm">*</span>
                ) : (
                    ""
                )}
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
                className={`flex justify-between cursor-pointer ${
                    styles?.selectClasses ??
                    "border-gray-300 p-2 border rounded-lg w-full text-gray-700 sm:text-sm"
                }`}
            >
                <div className="flex justify-between items-center w-full">
                    {selected.length > 0 ? (
                        <div className="flex flex-wrap items-center gap-1">
                            {selected.map((option, index) => (
                                <div
                                    className="flex flex-wrap gap-1"
                                    key={index}
                                >
                                    <span
                                        className={`${
                                            styles?.selectedItemsBadgeClasses ??
                                            "bg-gray-500 hover:bg-red-400 p-0.5 rounded-sm text-white "
                                        } cursor-pointer`}
                                        onClick={(e) =>
                                            handleRemoveFromSelected(e, option)
                                        }
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
                                    <LoadingSpinner className="w-full h-full text-primary" />
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
                                " px-3 pb-3 rounded-lg border border-gray-200 shadow-2xl bg-white-secondary dark:bg-dark-secondary w-full"
                            }`
                            : "hidden"
                    }
                    style={{
                        top: `${
                            (fullContainer?.current?.clientHeight ?? 0) + 5
                        }px`,
                        maxHeight: `${
                            styles?.dropDownContainerMaxHeight ?? "200"
                        }px`,
                        overflowY: "scroll",
                    }}
                    onScroll={(e) => handleDataScrolling(e)}
                >
                    <div className={`sticky top-0 bg-inherit`}>
                        <input
                            className={`${
                                styles?.searchInputClasses ??
                                "focus:border-primary p-1 my-2 focus:outline-primary rounded-md w-full dark:bg-secondary placeholder-white dark:text-white"
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
                            className={`
                            ${
                                include(getOption(item), selected)
                                    ? `${
                                        styles?.selectedDropDownItemClasses ??
                                        "bg-primary border-primary"
                                    }`
                                    : ""
                            }
                            ${
                                styles?.dropDownItemClasses ??
                                "cursor-pointer hover:border-primary hover:bg-primary my-1 p-2 rounded-md w-full text-black dark:text-white"
                            }`}
                            onClick={(e) => handleChoseItem(e, item)}
                        >
                            {getOption(item).label ?? ""}
                        </div>
                    ))}

                    {isLoading && (
                        <div className="flex justify-center items-center my-2 w-full dark:text-white">
                            Loading ...
                        </div>
                    )}
                </div>
            </div>
            {error ? <p className={"text-red-700 text-sm"}>{error}</p> : ""}
        </div>
    );
}

const include = (option: Option, selected: Option[]): boolean =>
    selected.filter((op) => isEqual(op, option)).length > 0;

export default ApiSelect;
