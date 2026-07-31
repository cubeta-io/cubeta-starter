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
import { Blockquote } from "@tiptap/extension-blockquote";
import { BulletList } from "@tiptap/extension-bullet-list";
import { CharacterCount } from "@tiptap/extension-character-count";
import { Color } from "@tiptap/extension-color";
import { FontFamily } from "@tiptap/extension-font-family";
import Gapcursor from "@tiptap/extension-gapcursor";
import { Heading } from "@tiptap/extension-heading";
import { HorizontalRule } from "@tiptap/extension-horizontal-rule";
import Image from "@tiptap/extension-image";
import Link from "@tiptap/extension-link";
import { ListItem } from "@tiptap/extension-list-item";
import { OrderedList } from "@tiptap/extension-ordered-list";
import { Table } from "@tiptap/extension-table";
import TableCell from "@tiptap/extension-table-cell";
import TableHeader from "@tiptap/extension-table-header";
import TableRow from "@tiptap/extension-table-row";
import TextAlign from "@tiptap/extension-text-align";
import { TextStyle } from "@tiptap/extension-text-style";
import { Youtube } from "@tiptap/extension-youtube";
import { Editor, EditorProvider, useCurrentEditor } from "@tiptap/react";
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
}

const TiptapEditor: React.FC<IProps> = ({
  onChange,
  className,
  minHeight,
  defaultValue,
  extraButtons,
  ...props
}) => {
  const [isDirty, setIsDirty] = useState(false);
  const [isSaving, setIsSaving] = useState(false);
  const [isSaved, setIsSaved] = useState(true);
  const saveTimeout = useRef<number | null>(null);
  const editorRef = useRef<any>(null);

  const saveContent = useCallback(() => {
    if (onChange && editorRef.current) {
      setIsSaving(true);
      const html = WrapContent(editorRef.current.getHTML());
      onChange(html);
      setIsSaved(true);
      setIsDirty(false);
      setIsSaving(false);
    }
  }, [onChange]);

  useEffect(() => {
    if (isDirty) {
      setIsSaved(false);
      if (saveTimeout.current) clearTimeout(saveTimeout.current);
      saveTimeout.current = setTimeout(() => {
        saveContent();
      }, 1500);
    }
    return () => {
      if (saveTimeout.current) clearTimeout(saveTimeout.current);
    };
  }, [isDirty, saveContent]);

  useEffect(() => {
    return () => {
      if (saveTimeout.current) clearTimeout(saveTimeout.current);
    };
  }, []);

  const handleUpdate = (event: any) => {
    editorRef.current = event.editor;
    setIsDirty(true);
  };

  return (
    <div
      className={className ?? "w-full rounded-lg border p-4 shadow-lg"}
      {...props}
    >
      <div className="mb-2 flex items-center justify-end gap-2">
        <Button
          onClick={saveContent}
          disabled={isSaved || isSaving}
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
  return (
    <div className="mb-4 flex w-full max-w-full flex-wrap items-center gap-2">
      <BoldButton editor={editor} />
      <ItalicButton editor={editor} />
      <ColorButton editor={editor} />
      <FontFamilyButton editor={editor} />
      <BlockQuoteButton editor={editor} />
      <BulletListButton editor={editor} />
      <OrderedListButton editor={editor} />
      <HeadingButton editor={editor} />
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
  StarterKit,
  Link.configure({
    openOnClick: true,
  }),
  Image,
  TextAlign.configure({
    types: ["heading", "paragraph"],
  }),
  Blockquote,
  BulletList,
  Heading.configure({
    levels: [1, 2, 3, 4, 5, 6],
  }),
  HorizontalRule,
  OrderedList,
  ListItem,
  Gapcursor,
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
