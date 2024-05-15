const LongTextField = ({
  label,
  value,
}: {
  label?: string;
  value?: string;
}) => {
  return (
    <div className="bg-gray-50 mb-5 p-4 rounded-md w-full font-bold text-xl">
      <label className="font-semibold text-lg">{label} :</label>
      <div className="border-0 p-4 rounded-md w-full outlin outline-0">
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
