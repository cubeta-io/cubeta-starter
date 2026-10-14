import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuPortal,
  DropdownMenuSub,
  DropdownMenuSubContent,
  DropdownMenuSubTrigger,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Editor } from "@tiptap/react";
import { TableIcon } from "lucide-react";
import React, { useState } from "react";
import { useTranslation } from "@/hooks/use-translation";

interface TableGridSelectorProps {
  onSelection: (rows: number, columns: number) => void;
  maxRows?: number;
  maxColumns?: number;
}

const TableGridSelector: React.FC<TableGridSelectorProps> = ({
  onSelection,
  maxRows = 10,
  maxColumns = 10,
}) => {
  const { t } = useTranslation();
  const [hoveredRow, setHoveredRow] = useState<number>(0);
  const [hoveredColumn, setHoveredColumn] = useState<number>(0);

  const handleMouseEnter = (row: number, col: number) => {
    setHoveredRow(row);
    setHoveredColumn(col);
  };

  const handleGridClick = () => {
    onSelection(hoveredRow, hoveredColumn);
  };

  return (
    <div className="w-fit p-4">
      <div className="flex flex-col items-center">
        <div className="grid gap-1">
          {Array.from({ length: maxRows }).map((_, rowIndex) => (
            <div className="flex gap-1" key={`row-${rowIndex}`}>
              {Array.from({ length: maxColumns }).map((_, colIndex) => (
                <div
                  key={`col-${colIndex}`}
                  className={`h-6 w-6 rounded-sm border ${
                    rowIndex < hoveredRow && colIndex < hoveredColumn
                      ? "bg-primary border-primary"
                      : "border-gray-300"
                  }`}
                  onMouseEnter={() =>
                    handleMouseEnter(rowIndex + 1, colIndex + 1)
                  }
                  onClick={handleGridClick}
                />
              ))}
            </div>
          ))}
        </div>
        <p className="mt-4 text-sm">
          {hoveredRow > 0 && hoveredColumn > 0
            ? t("dashboard.editor.selected_grid", {
                rows: hoveredRow,
                columns: hoveredColumn,
              })
            : t("dashboard.editor.select_grid")}
        </p>
      </div>
    </div>
  );
};

export const TableButtons = ({ editor }: { editor: Editor }) => {
  const [open, setOpen] = useState(false);
  const { t } = useTranslation();
  return (
    <DropdownMenu open={open} onOpenChange={setOpen}>
      <DropdownMenuTrigger
        render={
          <Button variant="outline" size="icon">
            <TableIcon />
          </Button>
        }
      />
      <DropdownMenuContent align={"start"}>
        <DropdownMenuSub>
          <DropdownMenuSubTrigger>
            {t("dashboard.editor.insert_table")}
          </DropdownMenuSubTrigger>
          <DropdownMenuPortal>
            <DropdownMenuSubContent>
              <TableGridSelector
                onSelection={(rows, columns) => {
                  setOpen(false);
                  editor
                    .chain()
                    .focus()
                    .insertTable({
                      rows: rows,
                      cols: columns,
                      withHeaderRow: true,
                    })
                    .run();
                }}
              />
            </DropdownMenuSubContent>
          </DropdownMenuPortal>
        </DropdownMenuSub>
        <DropdownMenuSub>
          <DropdownMenuSubTrigger>
            {t("dashboard.editor.add_column")}
          </DropdownMenuSubTrigger>
          <DropdownMenuPortal>
            <DropdownMenuSubContent>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().addColumnBefore().run()}
              >
                {t("dashboard.editor.before")}
              </DropdownMenuItem>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().addColumnAfter().run()}
              >
                {t("dashboard.editor.after")}
              </DropdownMenuItem>
            </DropdownMenuSubContent>
          </DropdownMenuPortal>
        </DropdownMenuSub>
        <DropdownMenuSub>
          <DropdownMenuSubTrigger>
            {t("dashboard.editor.add_row")}
          </DropdownMenuSubTrigger>
          <DropdownMenuPortal>
            <DropdownMenuSubContent>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().addRowBefore().run()}
              >
                {t("dashboard.editor.before")}
              </DropdownMenuItem>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().addRowAfter().run()}
              >
                {t("dashboard.editor.after")}
              </DropdownMenuItem>
            </DropdownMenuSubContent>
          </DropdownMenuPortal>
        </DropdownMenuSub>

        <DropdownMenuSub>
          <DropdownMenuSubTrigger>
            {t("dashboard.actions.delete")}
          </DropdownMenuSubTrigger>
          <DropdownMenuPortal>
            <DropdownMenuSubContent>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().deleteColumn().run()}
              >
                {t("dashboard.editor.delete_column")}
              </DropdownMenuItem>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().deleteRow().run()}
              >
                {t("dashboard.editor.delete_row")}
              </DropdownMenuItem>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().deleteTable().run()}
              >
                {t("dashboard.editor.delete_table")}
              </DropdownMenuItem>
            </DropdownMenuSubContent>
          </DropdownMenuPortal>
        </DropdownMenuSub>

        <DropdownMenuSub>
          <DropdownMenuSubTrigger>
            {t("dashboard.editor.cells")}
          </DropdownMenuSubTrigger>
          <DropdownMenuPortal>
            <DropdownMenuSubContent>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().mergeCells().run()}
              >
                {t("dashboard.editor.merge_cells")}
              </DropdownMenuItem>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().splitCell().run()}
              >
                {t("dashboard.editor.split_cell")}
              </DropdownMenuItem>
            </DropdownMenuSubContent>
          </DropdownMenuPortal>
        </DropdownMenuSub>

        <DropdownMenuSub>
          <DropdownMenuSubTrigger>
            {t("dashboard.editor.header")}
          </DropdownMenuSubTrigger>
          <DropdownMenuPortal>
            <DropdownMenuSubContent>
              <DropdownMenuItem
                onClick={() =>
                  editor.chain().focus().toggleHeaderColumn().run()
                }
              >
                {t("dashboard.editor.toggle_header_column")}
              </DropdownMenuItem>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().toggleHeaderRow().run()}
              >
                {t("dashboard.editor.toggle_header_row")}
              </DropdownMenuItem>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().toggleHeaderCell().run()}
              >
                {t("dashboard.editor.toggle_header_cell")}
              </DropdownMenuItem>
            </DropdownMenuSubContent>
          </DropdownMenuPortal>
        </DropdownMenuSub>

        <DropdownMenuItem
          onClick={() => editor.chain().focus().fixTables().run()}
        >
          {t("dashboard.editor.fix_tables")}
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
};
