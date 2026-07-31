import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuRadioGroup,
  DropdownMenuRadioItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Input } from "@/components/ui/input";
import { Level } from "@tiptap/extension-heading";
import { Editor, useCurrentEditor } from "@tiptap/react";
import {
  Bold,
  Columns2,
  Columns3,
  Columns4,
  Heading1,
  Heading2,
  Heading3,
  Heading4,
  Heading5,
  Heading6,
  HeadingIcon,
  ImageIcon,
  Italic,
  Link2,
  List,
  ListOrdered,
  MonitorPlay,
  QuoteIcon,
  SeparatorHorizontal,
} from "lucide-react";
import { useState } from "react";

export const GetEditor = () => {
  const { editor } = useCurrentEditor();
  if (!editor) {
    throw new Error(
      "No editor found , when using `useCurrentEditor` hook you need to make sure that your component is wrapped with the EditorProvider component",
    );
  }

  return editor;
};

export const BulletListButton = ({ editor }: { editor: Editor }) => {
  return (
    <Button
      type={"button"}
      size={"icon"}
      variant={editor?.isActive("bulletList") ? "default" : "outline"}
      onClick={() => editor.chain().focus().toggleBulletList().run()}
    >
      <List />
    </Button>
  );
};

export const BoldButton = ({ editor }: { editor: Editor }) => {
  return (
    <Button
      onClick={() => editor.chain().focus().toggleBold().run()}
      variant={editor.isActive("bold") ? "default" : "outline"}
      type={"button"}
      size={"icon"}
    >
      <Bold />
    </Button>
  );
};

export const ItalicButton = ({ editor }: { editor: Editor }) => {
  return (
    <Button
      onClick={() => editor.chain().focus().toggleItalic().run()}
      size={"icon"}
      variant={editor.isActive("italic") ? "default" : "outline"}
      type={"button"}
    >
      <Italic className="h-5 w-5" />
    </Button>
  );
};

export const BlockQuoteButton = ({ editor }: { editor: Editor }) => {
  return (
    <Button
      type={"button"}
      variant={editor.isActive("blockquote") ? "default" : "outline"}
      onClick={() => editor.chain().focus().toggleBlockquote().run()}
      size={"icon"}
    >
      <QuoteIcon />
    </Button>
  );
};

export const LinkButton = ({ editor }: { editor: Editor }) => {
  const [url, setUrl] = useState("");
  return (
    <AlertDialog>
      <AlertDialogTrigger
        render={
          <Button size={"icon"} type={"button"} variant={"outline"}>
            <Link2 />
          </Button>
        }
      />
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Add your url</AlertDialogTitle>
        </AlertDialogHeader>
        <div className={"my-4"}>
          <Input type={"url"} onChange={(e) => setUrl(e.target.value)} />
        </div>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction
            onClick={() =>
              editor.chain().focus().toggleLink({ href: url }).run()
            }
          >
            Insert
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
};

export const ImageUrlButton = ({ editor }: { editor: Editor }) => {
  const [url, setUrl] = useState("");
  return (
    <AlertDialog>
      <AlertDialogTrigger
        render={
          <Button size={"icon"} type={"button"} variant={"outline"}>
            <ImageIcon />
          </Button>
        }
      />
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Add your image url</AlertDialogTitle>
        </AlertDialogHeader>
        <div className={"my-4"}>
          <Input type={"url"} onChange={(e) => setUrl(e.target.value)} />
        </div>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction
            onClick={() => editor.chain().focus().setImage({ src: url }).run()}
          >
            Insert
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
};

export const HeadingButton = ({ editor }: { editor: Editor }) => {
  let currentToggled;

  const isActive = (level: Level) =>
    editor?.isActive("heading", { level: level });

  switch (true) {
    case isActive(1):
      currentToggled = "1";
      break;
    case isActive(2):
      currentToggled = "2";
      break;
    case isActive(3):
      currentToggled = "3";
      break;
    case isActive(4):
      currentToggled = "4";
      break;
    case isActive(5):
      currentToggled = "5";
      break;
    case isActive(6):
      currentToggled = "6";
      break;
    default:
      currentToggled = undefined;
      break;
  }

  return (
    <DropdownMenu>
      <DropdownMenuTrigger
        render={
          <Button variant="outline" size="icon">
            <HeadingIcon />
          </Button>
        }
      />
      <DropdownMenuContent align={"start"}>
        <DropdownMenuRadioGroup
          value={currentToggled}
          onValueChange={(value) => {
            editor
              .chain()
              .focus()
              .setHeading({ level: parseInt(value) as Level })
              .run();
          }}
        >
          <DropdownMenuRadioItem value={"1"}>
            <Heading1 />
          </DropdownMenuRadioItem>

          <DropdownMenuRadioItem value={"2"}>
            <Heading2 />
          </DropdownMenuRadioItem>

          <DropdownMenuRadioItem value={"3"}>
            <Heading3 />
          </DropdownMenuRadioItem>

          <DropdownMenuRadioItem value={"4"}>
            <Heading4 />
          </DropdownMenuRadioItem>

          <DropdownMenuRadioItem value={"5"}>
            <Heading5 />
          </DropdownMenuRadioItem>

          <DropdownMenuRadioItem value={"6"}>
            <Heading6 />
          </DropdownMenuRadioItem>
        </DropdownMenuRadioGroup>
      </DropdownMenuContent>
    </DropdownMenu>
  );
};

export const HorizontalRuleButton = ({ editor }: { editor: Editor }) => {
  return (
    <Button
      type={"button"}
      size={"icon"}
      variant={"outline"}
      onClick={() => editor.chain().focus().setHorizontalRule().run()}
    >
      <SeparatorHorizontal />
    </Button>
  );
};

export const OrderedListButton = ({ editor }: { editor: Editor }) => {
  return (
    <Button
      size={"icon"}
      type={"button"}
      variant={"outline"}
      onClick={() => editor.chain().focus().toggleOrderedList().run()}
    >
      <ListOrdered />
    </Button>
  );
};

export const YoutubeButton = ({ editor }: { editor: Editor }) => {
  const [url, setUrl] = useState<string | undefined>(undefined);

  return (
    <AlertDialog>
      <AlertDialogTrigger
        render={
          <Button type={"button"} variant={"outline"} size={"icon"}>
            <MonitorPlay />
          </Button>
        }
      />
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Add your youtube video url</AlertDialogTitle>
        </AlertDialogHeader>
        <Input type={"url"} onChange={(e) => setUrl(e.target?.value)} />
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction
            onClick={() => {
              if (url) {
                editor.commands.setYoutubeVideo({
                  src: url,
                  width: 640,
                  height: 480,
                });
              }
            }}
          >
            Insert
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  );
};

export const ColorButton = ({ editor }: { editor: Editor }) => {
  return (
    <Input
      type="color"
      onInput={(event) =>
        // @ts-ignore
        editor.chain().focus().setColor(event.target.value).run()
      }
      value={editor.getAttributes("textStyle").color}
      className={"size-9"}
    />
  );
};

export const FontFamilyButton = ({ editor }: { editor: Editor }) => {
  let currentToggled;

  const isActive = (family: string) =>
    editor.isActive("textStyle", {
      fontFamily: family,
    });

  switch (true) {
    case isActive("Inter"):
      currentToggled = "Inter";
      break;
    case isActive('"Comic Sans MS", "Comic Sans"'):
      currentToggled = '"Comic Sans MS", "Comic Sans"';
      break;
    case isActive("serif"):
      currentToggled = "serif";
      break;
    case isActive("monospace"):
      currentToggled = "monospace";
      break;
    case isActive("cursive"):
      currentToggled = "cursive";
      break;
    default:
      currentToggled = undefined;
      break;
  }

  return (
    <DropdownMenu>
      <DropdownMenuTrigger
        render={
          <Button size={"icon"} type={"button"} variant={"outline"}>
            F
          </Button>
        }
      />

      <DropdownMenuContent align={"start"}>
        <DropdownMenuRadioGroup
          value={currentToggled}
          onValueChange={(value) => {
            if (value == "none") {
              // editor.chain().focus().unsetFontFamily().run();
            } else {
              editor.chain().focus().setFontFamily(value).run();
            }
          }}
        >
          <DropdownMenuRadioItem value={"Inter"}>Inter</DropdownMenuRadioItem>
          <DropdownMenuRadioItem value={'"Comic Sans MS", "Comic Sans"'}>
            Comic Sans MS , Comic Sans
          </DropdownMenuRadioItem>

          <DropdownMenuRadioItem value={"serif"}>Serif</DropdownMenuRadioItem>
          <DropdownMenuRadioItem value={"monospace"}>
            Monospace
          </DropdownMenuRadioItem>
          <DropdownMenuRadioItem value={"cursive"}>
            Cursive
          </DropdownMenuRadioItem>
          <DropdownMenuRadioItem value={"none"}>
            Unset font family
          </DropdownMenuRadioItem>
        </DropdownMenuRadioGroup>
      </DropdownMenuContent>
    </DropdownMenu>
  );
};

export const ColumnsButton = ({ editor }: { editor: Editor }) => {
  return (
    <DropdownMenu>
      <DropdownMenuTrigger
        render={
          <Button variant="outline" size="icon">
            <Columns2 />
          </Button>
        }
      />
      <DropdownMenuContent align={"start"} className={"min-w-10"}>
        <DropdownMenuRadioGroup
          onValueChange={(value) => {
            editor.commands.insertColumns(parseInt(value));
          }}
        >
          <DropdownMenuRadioItem
            className={"flex w-full items-center justify-between px-1"}
            value={"2"}
          >
            <Columns2 />
            <span>2</span>
          </DropdownMenuRadioItem>
          <DropdownMenuRadioItem
            className={"flex w-full items-center justify-between px-1"}
            value={"3"}
          >
            <Columns3 />
            <span>3</span>
          </DropdownMenuRadioItem>

          <DropdownMenuRadioItem
            className={"flex w-full items-center justify-between px-1"}
            value={"4"}
          >
            <Columns4 />
            <span>4</span>
          </DropdownMenuRadioItem>
        </DropdownMenuRadioGroup>
      </DropdownMenuContent>
    </DropdownMenu>
  );
};
