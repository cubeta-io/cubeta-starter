import * as React from "react";
import { format, parseISO } from "date-fns";
import { ChevronDownIcon } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Calendar } from "@/components/ui/calendar";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from "@/components/ui/popover";
import { cn } from "@/lib/utils";
import { usePage } from "@inertiajs/react";

type CalendarProps = React.ComponentProps<typeof Calendar>;

export interface DatePickerProps extends Omit<
  CalendarProps,
  "mode" | "selected" | "onSelect"
> {
  name: string;
  label?: string;
  placeholder?: string;
  value?: Date | string;
  defaultValue?: Date | string;
  onChange?: (date: Date | undefined) => void;
  required?: boolean;
  disabled?: boolean;
  className?: string;
}

function normalizeDate(value: Date | string | undefined): Date | undefined {
  if (!value) return undefined;
  return value instanceof Date ? value : parseISO(value);
}

const DatePicker: React.FC<DatePickerProps> = ({
  name,
  label,
  placeholder = "Pick a date",
  value,
  defaultValue,
  onChange,
  required = false,
  disabled = false,
  className,
  ...calendarProps
}) => {
  const {
    props: { errors },
  } = usePage();
  const error = name && errors?.[name] ? errors[name] : undefined;

  const isControlled = value !== undefined;
  const [internalDate, setInternalDate] = React.useState<Date | undefined>(
    normalizeDate(defaultValue),
  );

  const date = isControlled ? normalizeDate(value) : internalDate;
  const formattedValue = date ? format(date, "yyyy-MM-dd") : "";

  const handleSelect = React.useCallback(
    (selected: Date | undefined) => {
      if (!isControlled) {
        setInternalDate(selected);
      }
      onChange?.(selected);
    },
    [isControlled, onChange],
  );

  return (
    <Field className={className}>
      {label && (
        <FieldLabel htmlFor={`${name}_id`}>
          {label}
          {required && <span className="text-destructive">*</span>}
        </FieldLabel>
      )}
      <Popover>
        <PopoverTrigger
          render={
            <Button
              id={`${name}_id`}
              name={name}
              type="button"
              variant="outline"
              disabled={disabled}
              data-empty={!date}
              className={cn(
                "data-[empty=true]:text-muted-foreground w-full justify-between text-left font-normal",
                error && "border-destructive",
              )}
            >
              {date ? format(date, "PPP") : <span>{placeholder}</span>}
              <ChevronDownIcon data-icon="inline-end" />
            </Button>
          }
        />
        <PopoverContent className="w-auto p-0" align="start">
          <Calendar
            mode="single"
            selected={date}
            onSelect={handleSelect}
            defaultMonth={date}
            disabled={disabled}
            {...calendarProps}
          />
        </PopoverContent>
      </Popover>
      <input type="hidden" name={name} value={formattedValue} />
      {error && <FieldError>{error}</FieldError>}
    </Field>
  );
};

export default DatePicker;
