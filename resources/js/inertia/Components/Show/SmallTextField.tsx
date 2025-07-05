const SmallTextField = ({ label, value }: { label?: string; value?: any }) => {
  return (
    <div className="dark:bg-dark mb-5 flex w-full items-center justify-between rounded-md bg-gray-50 p-4 text-xl font-bold dark:text-white">
      <label className="text-lg font-semibold">{label} :</label>
      <span>{value}</span>
    </div>
  );
};

export default SmallTextField;
