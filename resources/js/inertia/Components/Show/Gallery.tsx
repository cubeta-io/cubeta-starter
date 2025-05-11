import ImagePreview from "@/Components/Show/ImagePreview";

const Gallery = ({
  sources,
}: {
  sources: (string | undefined)[] | undefined;
}) => {
  return (
    <div
      className={`dark:bg-dark grid w-full grid-cols-4 gap-5 dark:text-white`}
    >
      {sources?.map(
        (img: string | undefined, index) =>
          img && (
            <div key={index} className="h-40">
              <ImagePreview src={img} />
            </div>
          ),
      )}
    </div>
  );
};

export default Gallery;
