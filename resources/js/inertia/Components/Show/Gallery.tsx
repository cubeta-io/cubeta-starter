import ImagePreview from "./ImagePreview";

const Gallery = ({sources}: { sources: (string | undefined)[] | undefined }) => {
    return (
        <div className={`grid grid-cols-4 gap-5 w-full dark:bg-dark dark:text-white`}>
            {sources?.map((img: string | undefined, index) => (
                img && (
                    <div key={index} className="h-40">
                        <ImagePreview src={img}/>
                    </div>
                )
            ))}
        </div>
    );
};

export default Gallery;
