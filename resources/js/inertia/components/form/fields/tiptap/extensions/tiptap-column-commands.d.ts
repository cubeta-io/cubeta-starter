import "@tiptap/core";

declare module "@tiptap/core" {
  interface Commands<ReturnType> {
    insertColumns: {
      /**
       * Insert a columns block with the given number of columns
       */
      insertColumns: (numColumns?: number) => ReturnType;
    };
  }
}
