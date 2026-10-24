import {
  Column,
  Columns,
} from "@/components/form/fields/tiptap/extensions/columns";
import WrapContent from "@/components/form/fields/tiptap/style-stored-content";
import { TableButtons } from "@/components/form/fields/tiptap/table-buttons";
import {
  BlockQuoteButton,
  BoldButton,
  BulletListButton,
  ColorButton,
  ColumnsButton,
  FontFamilyButton,
  HeadingButton,
  HorizontalRuleButton,
  ImageUrlButton,
  ItalicButton,
  LinkButton,
  OrderedListButton,
  YoutubeButton,
} from "@/components/form/fields/tiptap/tiptap-buttons";
import TiptapFooter from "@/components/form/fields/tiptap/tiptap-footer";
import { Button } from "@/components/ui/button";
import { CharacterCount } from "@tiptap/extension-character-count";
import { Color } from "@tiptap/extension-color";
import { FontFamily } from "@tiptap/extension-font-family";
import Image from "@tiptap/extension-image";
import { Table } from "@tiptap/extension-table";
import TableCell from "@tiptap/extension-table-cell";
import TableHeader from "@tiptap/extension-table-header";
import TableRow from "@tiptap/extension-table-row";
import TextAlign from "@tiptap/extension-text-align";
import { TextStyle } from "@tiptap/extension-text-style";
import { Youtube } from "@tiptap/extension-youtube";
import {
  Editor,
  EditorProvider,
  useCurrentEditor,
  useEditorState,
} from "@tiptap/react";
import StarterKit from "@tiptap/starter-kit";
import { Save } from "lucide-react";
import React, {
  HTMLAttributes,
  useCallback,
  useEffect,
  useRef,
  useState,
} from "react";
import "./tiptap.css";

interface IProps extends Omit<HTMLAttributes<HTMLDivElement>, "onChange"> {
  onChange?: (value: string) => void;
  minHeight?: string;
  defaultValue?: string;
  extraButtons?: (editor: Editor) => React.ReactNode;
  debounceDelay?: number;
}

const TiptapEditor: React.FC<IProps> = ({
  onChange,
  className,
  minHeight,
  defaultValue,
  extraButtons,
  debounceDelay = 0,
  ...props
}) => {
  const [isSaved, setIsSaved] = useState(true);
  const saveTimeout = useRef<number | null>(null);
  const editorRef = useRef<Editor | null>(null);
  const onChangeRef = useRef(onChange);

  onChangeRef.current = onChange;

  const saveContent = useCallback(() => {
    if (saveTimeout.current !== null) {
      window.clearTimeout(saveTimeout.current);
      saveTimeout.current = null;
    }

    if (onChangeRef.current && editorRef.current) {
      const html = WrapContent(editorRef.current.getHTML());
      onChangeRef.current(html);
      setIsSaved(true);
    }
  }, []);

  useEffect(() => {
    return () => {
      if (saveTimeout.current !== null) {
        window.clearTimeout(saveTimeout.current);
      }
    };
  }, []);

  const handleUpdate = useCallback(
    ({ editor }: { editor: Editor }) => {
      editorRef.current = editor;

      if (saveTimeout.current !== null) {
        window.clearTimeout(saveTimeout.current);
      }

      queueMicrotask(() => setIsSaved(false));
      saveTimeout.current = window.setTimeout(saveContent, debounceDelay);
    },
    [debounceDelay, saveContent],
  );

  return (
    <div
      className={className ?? "w-full rounded-lg border p-4 shadow-lg"}
      {...props}
    >
      <div className="mb-2 flex items-center justify-end gap-2">
        <Button
          onClick={saveContent}
          disabled={isSaved}
          variant="default"
          size="sm"
          type="button"
        >
          <Save />
        </Button>
      </div>
      <EditorProvider
        extensions={Extensions}
        onUpdate={handleUpdate}
        content={defaultValue}
        shouldRerenderOnTransaction={false}
        slotBefore={<EditorToolbar extraButtons={extraButtons} />}
        slotAfter={<TiptapFooter />}
        editorContainerProps={{
          className: "border p-4 rounded-lg",
          onClick: (e) => {
            e.preventDefault();
            const proseMirrorNode =
              // @ts-ignore
              e.target.childNodes?.item(0);
            if (proseMirrorNode) {
              // @ts-ignore
              proseMirrorNode.focus();
            }
          },
          style: {
            minHeight: minHeight ?? "200px",
          },
        }}
      />
    </div>
  );
};

export default TiptapEditor;

const EditorToolbar = ({
  extraButtons,
}: {
  extraButtons?: (editor: Editor) => React.ReactNode;
}) => {
  const { editor } = useCurrentEditor();
  if (!editor) {
    throw new Error(
      "No editor found , when using `useCurrentEditor` hook you need to make sure that your component is wrapped with the EditorProvider component",
    );
  }

  const editorState = useEditorState({
    editor,
    selector: ({ editor: currentEditor }) => ({
      isBold: currentEditor.isActive("bold"),
      isItalic: currentEditor.isActive("italic"),
      isBlockquote: currentEditor.isActive("blockquote"),
      isBulletList: currentEditor.isActive("bulletList"),
      isOrderedList: currentEditor.isActive("orderedList"),
      headingLevel: ([1, 2, 3, 4, 5, 6] as const).find((level) =>
        currentEditor.isActive("heading", { level }),
      ),
      color: currentEditor.getAttributes("textStyle").color as
        string | undefined,
      fontFamily: currentEditor.getAttributes("textStyle").fontFamily as
        string | undefined,
    }),
  });

  return (
    <div className="mb-4 flex w-full max-w-full flex-wrap items-center gap-2">
      <BoldButton editor={editor} isActive={editorState.isBold} />
      <ItalicButton editor={editor} isActive={editorState.isItalic} />
      <ColorButton editor={editor} color={editorState.color} />
      <FontFamilyButton editor={editor} fontFamily={editorState.fontFamily} />
      <BlockQuoteButton editor={editor} isActive={editorState.isBlockquote} />
      <BulletListButton editor={editor} isActive={editorState.isBulletList} />
      <OrderedListButton editor={editor} isActive={editorState.isOrderedList} />
      <HeadingButton editor={editor} currentLevel={editorState.headingLevel} />
      <HorizontalRuleButton editor={editor} />
      <LinkButton editor={editor} />
      <ImageUrlButton editor={editor} />
      <TableButtons editor={editor} />
      <YoutubeButton editor={editor} />
      <ColumnsButton editor={editor} />
      {extraButtons && extraButtons(editor)}
    </div>
  );
};

const Extensions = [
  StarterKit.configure({
    heading: {
      levels: [1, 2, 3, 4, 5, 6],
    },
    link: {
      openOnClick: true,
    },
  }),
  Image,
  TextAlign.configure({
    types: ["heading", "paragraph"],
  }),
  Table.configure({
    resizable: true,
  }),
  TableRow,
  TableHeader,
  TableCell,
  Youtube.configure({
    controls: true,
    nocookie: true,
  }),
  CharacterCount,
  Color,
  FontFamily,
  TextStyle,
  Columns,
  Column,
];
