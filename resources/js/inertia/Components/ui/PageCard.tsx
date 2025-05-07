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
    <div
      className={
        "bg-white-secondary dark:bg-dark-secondary w-full rounded-md p-8"
      }
      style={{
        boxShadow: "0 35px 60px 15px rgba(0, 0, 0, 0.2)",
      }}
    >
      {title || actions ? (
        <div
          className={`dark:bg-dark mb-5 flex w-full items-center justify-between rounded-md bg-white p-4 shadow-md`}
        >
          <h2 className="text-xl font-bold dark:text-white">{title}</h2>
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
