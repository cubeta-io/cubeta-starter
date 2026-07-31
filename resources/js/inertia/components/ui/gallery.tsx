import ImagePreview from "@/components/ui/image-preview";

const Gallery = ({
  sources,
}: {
  sources: (string | undefined)[] | undefined;
}) => {
  return (
    <div className={`grid w-full grid-cols-1 gap-5 md:grid-cols-4`}>
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