using DataCenter.Data.Enums;

using DataCenter.Data.Models.Audit;

using DataCenter.Infrastructure.Providers;
using DataCenter.Infrastructure.Providers.Interfaces;
using DataCenter.Infrastructure.Services.Audit.Interfaces;
using DataCenter.Infrastructure.Services.Interfaces;

using System.Threading.Tasks;

namespace DataCenter.Infrastructure.Services.Audit
{
    public class UserTrafficAuditService : IUserTrafficAuditService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        public UserTrafficAuditService(DatabaseProvider databaseProvider)
        {
            this.databaseProvider = databaseProvider;
        }

        public async Task SaveUserJoinAttempt(string unitId, string userName)
        {
            await CreateTrafficRecord(unitId, userName, UserTrafficType.Join);
        }

        public async Task SaveUserQuitAttempt(string unitId, string userName)
        {
            await CreateTrafficRecord(unitId, userName, UserTrafficType.Quit);
        }

        private async Task CreateTrafficRecord(string unitId, string userName, UserTrafficType userTrafficType)
        {
            UserTrafficAuditRecord userTrafficAuditRecord = new UserTrafficAuditRecord
            {
                Subject = userName,
                UnitId = unitId,
                UserTrafficType = userTrafficType
            };

            await databaseProvider.CreateAsync(userTrafficAuditRecord);
            await databaseProvider.CommitAsync();
        }
    }
}