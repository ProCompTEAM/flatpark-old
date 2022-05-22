using BoDi;
using DataCenter.Common.Mapping;
using DataCenter.Infrastructure.Controllers;
using DataCenter.Infrastructure.Providers;
using DataCenter.Infrastructure.Services;
using DataCenter.Infrastructure.Services.Audit;

namespace DataCenter.Infrastructure
{
    public static class Module
    {
        public static IObjectContainer Container { private set; get; }

        static Module()
        {
            Container = new ObjectContainer();

            ResolveMapper();

            ResolveProviders();
            ResolveServices();
            ResolveControllers();

            RestoreCredentials();
        }

        private static void RestoreCredentials()
        {
            Container.Resolve<AuthorizationProvider>()
                .RestoreCredentials();
        }

        private static void ResolveControllers()
        {
            Resolve<SettingsController>();
            Resolve<UsersController>();
            Resolve<MapController>();
            Resolve<FloatingTextsController>();
            Resolve<BanRecordsController>();
        }

        private static void ResolveProviders()
        {
            Resolve<DateTimeProvider>();
            Resolve<TokenProvider>();
            Resolve<DatabaseProvider>();
            Resolve<AuthorizationProvider>();
        }

        private static void ResolveServices()
        {
            Resolve<BanRecordsService>();
            Resolve<UsersService>();
            Resolve<MapService>();
            Resolve<FloatingTextsService>();

            Resolve<ExecutedCommandsAuditService>();
            Resolve<ChatMessagesAuditService>();
            Resolve<UserTrafficAuditService>();
        }

        private static void ResolveMapper()
        {
            Container.RegisterInstanceAs(CommonMapper.Instance);
        }

        private static void Resolve<T>()
        {
            Container.Resolve<T>();
        }
    }
}
