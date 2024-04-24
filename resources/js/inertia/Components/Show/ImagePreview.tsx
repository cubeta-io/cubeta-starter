import React, { HTMLProps, useState } from "react";
import XMark from "@/Components/icons/XMark";

interface ImgProps
    extends Omit<HTMLProps<HTMLImageElement>, "className" | "alt"> {
    caption?: string;
    src: string;
}

const ImagePreview: React.FC<ImgProps> = ({ caption, src, ...props }) => {
    const [isExpanded, setIsExpanded] = useState(false);

    return (
        <div
            className={`flex justify-center items-center  ${isExpanded ? "fixed top-0 left-0 z-50 w-full h-full bg-black opacity-95" : "h-full w-full"}`}
            onClick={() => setIsExpanded(false)}
        >
            {isExpanded && (
                <div
                    className={"fixed z-50 top-0 right-0 cursor-pointer"}
                    onClick={(e) => {
                        e.stopPropagation();
                        setIsExpanded(false);
                    }}
                >
                    <XMark
                        className={
                            "w-12 h-12 bg-gray-300 rounded-md text-black hover:text-white"
                        }
                    />
                </div>
            )}
            <div
                className={` rounded-md cursor-pointer transition duration-300 transform ${isExpanded ? "scale-110" : "h-full w-full object-contain overflow-hidden"}`}
                onClick={(e) => {
                    e.stopPropagation();
                    setIsExpanded(true);
                }}
            >
                <img
                    src={src}
                    className={`${isExpanded ? "max-w-fit max-h-fit" : "h-full w-full max-w-40 object-cover rounded-full "}`}
                    {...props}
                    alt={caption}
                />
                {isExpanded && (
                    <p
                        className={
                            "text-white text-xl bg-black text-center opacity-90"
                        }
                    >
                        {caption}
                    </p>
                )}
            </div>
        </div>
    );
};

export default ImagePreview;
