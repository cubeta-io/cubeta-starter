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
        <div className={"p-8 bg-white-secondary dark:bg-dark-secondary shadow-lg rounded-md w-full"}>
            {title || actions ? (
                <div
                    className={`rounded-md p-4 bg-white dark:bg-dark mb-5 flex items-center w-full shadow-md justify-between`}
                >
                    <h2 className="font-bold text-xl dark:text-white">{title}</h2>
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
