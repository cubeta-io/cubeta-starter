import { ReactNode } from "react";

const PageCard = ({
  children,
  title,
  actions,
}: {
  children?: ReactNode;
  title?: string;
  actions?: ReactNode;
}) => {
  return (
    <div className={"p-8 bg-white shadow-lg rounded-md w-full"}>
      {title || actions ? (
        <div
          className={`rounded-md p-4 bg-gray-100 mb-5 flex items-center w-full justify-between`}
        >
          <h2 className="font-bold text-xl">{title}</h2>
          <div>{actions ? actions : ""}</div>
        </div>
      ) : (
        ""
      )}
      {children}
    </div>
  );
};

export default PageCard;
