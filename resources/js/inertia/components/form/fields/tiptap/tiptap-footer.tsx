import { GetEditor } from "@/components/form/fields/tiptap/tiptap-buttons";
import { useEditorState } from "@tiptap/react";

const TiptapFooter = () => {
  const editor = GetEditor();
  const counts = useEditorState({
    editor,
    selector: ({ editor: currentEditor }) => ({
      characters: currentEditor.storage.characterCount.characters(),
      words: currentEditor.storage.characterCount.words(),
    }),
  });

  return (
    <div className={"mt-3 flex w-full items-center justify-between"}>
      <div className={"flex items-center gap-2 justify-self-end"}>
        <span>{counts.characters} Character</span>/
        <span>{counts.words} Word</span>
      </div>
    </div>
  );
};

export default TiptapFooter;
