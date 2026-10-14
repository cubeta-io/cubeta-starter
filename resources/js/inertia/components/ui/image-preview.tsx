import React, { HTMLProps, useState } from "react";
import { XIcon } from "lucide-react";
import { Button } from "@/components/ui/button";

interface ImgProps extends Omit<
  HTMLProps<HTMLImageElement>,
  "className" | "alt"
> {
  caption?: string;
  src: string;
}

const ImagePreview: React.FC<ImgProps> = ({ caption, src, ...props }) => {
  const [isExpanded, setIsExpanded] = useState(false);

  return (
    <div
      className={`flex items-center justify-center ${isExpanded ? "fixed top-0 left-0 z-50 h-full w-full bg-black opacity-95" : "h-full w-full"}`}
      onClick={() => setIsExpanded(false)}
    >
      {isExpanded && (
        <Button
          className={"fixed top-0 right-0 z-50 cursor-pointer"}
          onClick={(e) => {
            e.stopPropagation();
            setIsExpanded(false);
          }}
          size={"icon"}
        >
          <XIcon />
        </Button>
      )}
      <div
        className={`transform cursor-pointer rounded-md transition duration-300 ${isExpanded ? "scale-110" : "h-full w-full overflow-hidden object-contain"}`}
        onClick={(e) => {
          e.stopPropagation();
          setIsExpanded(true);
        }}
      >
        <img
          src={src}
          className={`${isExpanded ? "max-h-fit max-w-fit" : "size-32 rounded-full object-cover"}`}
          {...props}
          alt={caption}
        />
        {isExpanded && (
          <p className={"bg-black text-center text-xl text-white opacity-90"}>
            {caption}
          </p>
        )}
      </div>
    </div>
  );
};

export default ImagePreview;