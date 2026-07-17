interface Media {
  url: string;
  size: number;
  extension: string;
  mime_type: string;
}

export function getFileNameFromUrl(url: string): string {
  try {
    const parsedUrl = new URL(url);
    const pathname = parsedUrl.pathname;
    const fileName = pathname.substring(pathname.lastIndexOf("/") + 1);
    return decodeURIComponent(fileName);
  } catch (e) {
    console.error("Invalid URL:", url);
    return "";
  }
}

export function isMedia(obj: any): obj is Media {
  return (
    typeof obj === "object" &&
    obj !== null &&
    typeof obj?.url === "string" &&
    typeof obj.size === "number" &&
    typeof obj.extension === "string" &&
    typeof obj.mime_type === "string"
  );
}

export default Media;
