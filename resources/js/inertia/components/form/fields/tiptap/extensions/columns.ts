import { mergeAttributes, Node, RawCommands } from "@tiptap/core";
import { TextSelection } from "prosemirror-state";

// Column node (can contain any block, including columns for nesting)
const Column = Node.create({
  name: "column",
  group: "block",
  content: "block+",
  isolating: true,
  parseHTML() {
    return [
      {
        tag: 'div[data-type="column"]',
      },
    ];
  },
  renderHTML({ HTMLAttributes }) {
    return [
      "div",
      mergeAttributes(HTMLAttributes, {
        "data-type": "column",
        style: "flex: 1 1 0; min-width: 0;",
      }),
      0,
    ];
  },
});

// Columns node (can contain any number of columns)
const Columns = Node.create({
  name: "columns",
  group: "block",
  content: "column+",
  isolating: true,
  parseHTML() {
    return [
      {
        tag: 'div[data-type="columns"]',
      },
    ];
  },
  renderHTML({ HTMLAttributes }) {
    return [
      "div",
      mergeAttributes(HTMLAttributes, {
        "data-type": "columns",
        style: "display: flex; gap: 1em; width: 100%;",
      }),
      0,
    ];
  },
  addCommands() {
    return {
      insertColumns:
        (numColumns: number = 2) =>
        ({ tr, state, dispatch }) => {
          if (numColumns < 1) return false;
          const { schema } = state;
          const columnsType = schema.nodes.columns;
          const columnType = schema.nodes.column;
          if (!columnsType || !columnType) return false;

          // Create columns node with the specified number of empty columns
          const columnsNode = columnsType.create(
            {},
            Array.from({ length: numColumns }, () =>
              columnType.create({}, schema.nodes.paragraph.create()),
            ),
          );

          if (dispatch) {
            tr.replaceSelectionWith(columnsNode);

            // Move selection into the first paragraph of the first column
            const pos = tr.selection.$from.pos + 2;
            tr.setSelection(TextSelection.near(tr.doc.resolve(pos)));

            dispatch(tr.scrollIntoView());
          }
          return true;
        },
    } as Partial<RawCommands>;
  },
});

export { Column, Columns };
export default [Columns, Column];
