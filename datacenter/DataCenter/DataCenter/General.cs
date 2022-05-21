using DataCenter.Common;
using DataCenter.Common.Mapping;
using DataCenter.Common.Network;
using DataCenter.Data;
using DataCenter.Infrastructure;
using DataCenter.Infrastructure.Generic;
using DataCenter.Infrastructure.Generic.Interfaces;
using DataCenter.Infrastructure.Providers;
using System;
using System.Reflection;

namespace DataCenter
{
    public static class General
    {
        private static ILogger logger;

        private static ICrashLogger crashLogger;

        public static bool IsMainUnit { get; private set; } = false;

        public static DataCenterProperties Properties { get; set; }

        public static void LoadAll()
        {
            SetTitle("Starting...");

            IsMainUnit = true;

            InitializeAll();
        }

        public static void Log(string message, params object[] objs)
        {
            logger.Info(string.Format(message, objs), Defaults.LoggerDataCenterPrefix, ConsoleColor.White);
        }

        public static void Error(string message, params object[] objs)
        {
            logger.Info(string.Format(message, objs), Defaults.LoggerErrorPrefix, ConsoleColor.Red);
        }

        public static void Crash(string description, string[] traces)
        {
            crashLogger.Crash(description, traces);
        }

        public static void SetTitle(string titleMessage)
        {
            Console.Title = $"{ProductName} {Version}: {titleMessage}";
        }

        public static string Version => Assembly.GetExecutingAssembly().GetName().Version.ToString();

        public static string ProductName => Assembly.GetExecutingAssembly().GetName().Name;

        private static void InitializeAll()
        {
            CommonMapper.Initialize();

            Resolver.ResolveAll();

            Properties = new DataCenterProperties();
            logger = new Logger();
            crashLogger = new CrashLogger();

            Log("Loading database...");
            Database.Initialize();
            Database.MakeContext();

            Log("Loading web services...");
            NetSets.Initialize();

            LoadContextData();
        }

        private static void LoadContextData()
        {
            Resolver.Container.Resolve<AuthorizationProvider>()
                .RestoreCredentials();
        }
    }
}
