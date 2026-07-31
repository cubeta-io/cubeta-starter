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
            ? `Selected: ${hoveredRow} x ${hoveredColumn}`
            : "Hover over the grid to select"}
        </p>
      </div>
    </div>
  );
};

export const TableButtons = ({ editor }: { editor: Editor }) => {
  const [open, setOpen] = useState(false);
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
          <DropdownMenuSubTrigger>Inert Table</DropdownMenuSubTrigger>
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
          <DropdownMenuSubTrigger>Add column</DropdownMenuSubTrigger>
          <DropdownMenuPortal>
            <DropdownMenuSubContent>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().addColumnBefore().run()}
              >
                Before
              </DropdownMenuItem>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().addColumnAfter().run()}
              >
                After
              </DropdownMenuItem>
            </DropdownMenuSubContent>
          </DropdownMenuPortal>
        </DropdownMenuSub>
        <DropdownMenuSub>
          <DropdownMenuSubTrigger>Add row</DropdownMenuSubTrigger>
          <DropdownMenuPortal>
            <DropdownMenuSubContent>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().addRowBefore().run()}
              >
                Before
              </DropdownMenuItem>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().addRowAfter().run()}
              >
                After
              </DropdownMenuItem>
            </DropdownMenuSubContent>
          </DropdownMenuPortal>
        </DropdownMenuSub>

        <DropdownMenuSub>
          <DropdownMenuSubTrigger>Delete</DropdownMenuSubTrigger>
          <DropdownMenuPortal>
            <DropdownMenuSubContent>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().deleteColumn().run()}
              >
                Delete column
              </DropdownMenuItem>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().deleteRow().run()}
              >
                Delete row
              </DropdownMenuItem>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().deleteTable().run()}
              >
                Delete table
              </DropdownMenuItem>
            </DropdownMenuSubContent>
          </DropdownMenuPortal>
        </DropdownMenuSub>

        <DropdownMenuSub>
          <DropdownMenuSubTrigger>Cells</DropdownMenuSubTrigger>
          <DropdownMenuPortal>
            <DropdownMenuSubContent>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().mergeCells().run()}
              >
                Merge cells
              </DropdownMenuItem>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().splitCell().run()}
              >
                Split cell
              </DropdownMenuItem>
            </DropdownMenuSubContent>
          </DropdownMenuPortal>
        </DropdownMenuSub>

        <DropdownMenuSub>
          <DropdownMenuSubTrigger>Header</DropdownMenuSubTrigger>
          <DropdownMenuPortal>
            <DropdownMenuSubContent>
              <DropdownMenuItem
                onClick={() =>
                  editor.chain().focus().toggleHeaderColumn().run()
                }
              >
                Toggle header column
              </DropdownMenuItem>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().toggleHeaderRow().run()}
              >
                Toggle header row
              </DropdownMenuItem>
              <DropdownMenuItem
                onClick={() => editor.chain().focus().toggleHeaderCell().run()}
              >
                Toggle header cell
              </DropdownMenuItem>
            </DropdownMenuSubContent>
          </DropdownMenuPortal>
        </DropdownMenuSub>

        <DropdownMenuItem
          onClick={() => editor.chain().focus().fixTables().run()}
        >
          Fix tables
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  );
};
