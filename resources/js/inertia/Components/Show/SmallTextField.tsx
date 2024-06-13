const SmallTextField = ({ label, value }: { label?: string; value?: any }) => {
    return (
        <div className="flex justify-between items-center bg-gray-50 dark:bg-dark dark:text-white mb-5 p-4 rounded-md w-full font-bold text-xl">
            <label className="font-semibold text-lg">{label} :</label>
            <span>{value}</span>
        </div>
    );
};

export default SmallTextField;
