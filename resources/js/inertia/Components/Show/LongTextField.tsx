const LongTextField = ({
  label,
  value,
}: {
  label?: string;
  value?: string;
}) => {
  return (
    <div className="dark:bg-dark mb-5 w-full rounded-md bg-gray-50 p-4 text-xl font-bold dark:text-white">
      <label className="text-lg font-semibold">{label} :</label>
      <div className="outlin w-full rounded-md border-0 p-4 outline-0">
        <div
          dangerouslySetInnerHTML={{
            __html: value ?? "",
          }}
        />
      </div>
    </div>
  );
};

export default LongTextField;
