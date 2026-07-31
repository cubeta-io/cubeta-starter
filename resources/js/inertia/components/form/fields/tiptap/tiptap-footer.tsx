import { GetEditor } from "@/components/form/fields/tiptap/tiptap-buttons";

const TiptapFooter = () => {
  const editor = GetEditor();
  return (
    <div className={"mt-3 flex w-full items-center justify-between"}>
      <div className={"flex items-center gap-2 justify-self-end"}>
        <span>{editor.storage.characterCount.characters()} Character</span>/
        <span>{editor.storage.characterCount.words()} Word</span>
      </div>
    </div>
  );
};

export default TiptapFooter;
