import Media, { getFileNameFromUrl, isMedia } from "@/models/media";
import { usePage } from "@inertiajs/react";
import {
  FilePondInitialFile,
  ProcessServerConfigFunction,
  ServerUrl,
} from "filepond";
import "filepond-plugin-file-poster/dist/filepond-plugin-file-poster.css";
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css";
import "filepond/dist/filepond.min.css";
import { JSX, useState } from "react";
import { FilePond } from "react-filepond";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import { useTranslation } from "@/hooks/use-translation";

export type MediaInput = File | Media;

function toInitialFile(file: Media): FilePondInitialFile {
  return {
    source: file?.url,
    options: {
      type: "local",
      file: {
        name: getFileNameFromUrl(file?.url),
        size: file?.size,
        type: file?.mime_type,
      },
      metadata: {
        ...(file?.mime_type?.startsWith("image/") ? { poster: file?.url } : {}),
        media: file,
      },
    },
  };
}

function FilepondInput({
  name,
  isMultiple,
  label,
  acceptedFileTypes,
  onChange,
  defaultValue,
  process,
  allowProcess,
}: {
  name: string;
  isMultiple: true;
  label?: string;
  acceptedFileTypes?: string[];
  onChange?: (file: MediaInput[] | null) => void;
  defaultValue?: Media[];
  process?: string | ServerUrl | ProcessServerConfigFunction | null;
  allowProcess?: boolean;
}): JSX.Element;

function FilepondInput({
  name,
  isMultiple,
  label,
  acceptedFileTypes,
  onChange,
  defaultValue,
  process,
  allowProcess,
}: {
  name: string;
  isMultiple?: false | undefined;
  label?: string;
  acceptedFileTypes?: string[];
  onChange?: (file: MediaInput | null) => void;
  defaultValue?: Media;
  process?: string | ServerUrl | ProcessServerConfigFunction | null;
  allowProcess?: boolean;
}): JSX.Element;

function FilepondInput({
  name,
  isMultiple = false,
  label = undefined,
  acceptedFileTypes = ["image/jpeg", "image/png", "image/jpg"],
  onChange,
  defaultValue = [],
  process,
  allowProcess = true,
}: {
  name: string;
  isMultiple?: boolean;
  label?: string;
  acceptedFileTypes?: string[];
  onChange?:
    ((file: MediaInput[] | null) => void) | ((file: MediaInput | null) => void);
  defaultValue?: Media[] | Media;
  process?: string | ServerUrl | ProcessServerConfigFunction | null;
  allowProcess?: boolean;
}): JSX.Element {
  const { t } = useTranslation();
  defaultValue = defaultValue
    ? Array.isArray(defaultValue)
      ? defaultValue
      : [defaultValue]
    : [];

  const initialFiles: FilePondInitialFile[] | File[] = defaultValue.map(
    (file) => toInitialFile(file ?? {}),
  );

  const [files, setFiles] =
    useState<(FilePondInitialFile | File)[]>(initialFiles);

  const {
    props: { errors },
  } = usePage();
  const shouldValidateFileType = acceptedFileTypes.length > 0;

  return (
    <Field orientation={"vertical"}>
      {label && <FieldLabel>{label}</FieldLabel>}
      <FilePond
        labelIdle={t("dashboard.filepond.idle")}
        labelFileProcessing={t("dashboard.filepond.processing")}
        labelFileProcessingComplete={t(
          "dashboard.filepond.processing_complete",
        )}
        labelFileProcessingAborted={t("dashboard.filepond.processing_aborted")}
        labelFileProcessingError={t("dashboard.filepond.processing_error")}
        labelTapToCancel={t("dashboard.filepond.tap_cancel")}
        labelTapToRetry={t("dashboard.filepond.tap_retry")}
        labelTapToUndo={t("dashboard.filepond.tap_undo")}
        labelButtonRemoveItem={t("dashboard.filepond.remove")}
        labelButtonAbortItemProcessing={t("dashboard.filepond.abort")}
        labelButtonRetryItemProcessing={t("dashboard.filepond.retry")}
        files={files}
        onupdatefiles={(fileItems) => {
          const value = fileItems.map((item) => {
            const media = item.getMetadata("media");

            return isMedia(media) ? media : (item.file as File);
          });

          setFiles(
            value.map((item) => (isMedia(item) ? toInitialFile(item) : item)),
          );

          if (!onChange) {
            return;
          }
          if (isMultiple) {
            (onChange as (file: MediaInput[] | null) => void)(
              value.length > 0 ? value : null,
            );
          } else {
            (onChange as (file: MediaInput | null) => void)(
              value.length > 0 ? value[0] : null,
            );
          }
        }}
        acceptedFileTypes={
          shouldValidateFileType ? acceptedFileTypes : undefined
        }
        allowFileTypeValidation={shouldValidateFileType}
        allowMultiple={isMultiple}
        allowProcess={allowProcess}
        allowFilePoster={true}
        filePosterMaxHeight={250}
        filePosterMinHeight={250}
        server={{ process: process }}
      />
      {name && errors?.[name] && <FieldError>{errors[name]}</FieldError>}
    </Field>
  );
}

export default FilepondInput;
