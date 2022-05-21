namespace DataCenter.Common
{
    public static class Defaults
    {
        public const string LoggerDataCenterPrefix = "DataCenter";
        public const string LoggerErrorPrefix = "Error";

        public const string LogFilename = "datacenter.log";
        public const string CrashFolder = "dumps";

        public const string PropertiesDataCenterFilename = "datacenter.properties";
        public const string PropertiesDatabaseFilename = "database.properties";

        public const int DefaultStringLength = 128;

        public const int DefaultLongStringLength = 4096;

        public const int StartPhoneNumber = 10001;

        public const int MoneyRoundDigitsAmount = 2;

        public const double UnitStartBalance = 1000000000000;
    }
}