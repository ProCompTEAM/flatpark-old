using DataCenter.Infrastructure.Services.Audit.Interfaces;
using DataCenter.Infrastructure.Services.Interfaces;
using DataCenter.Infrastructure.Providers;
using DataCenter.Infrastructure.Providers.Interfaces;

using DataCenter.Data.Models.Audit;

using DataCenter.Common;

using System.Threading.Tasks;

namespace DataCenter.Infrastructure.Services.Audit
{
    public class ExecutedCommandsAuditService : IExecutedCommandsAuditService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        public ExecutedCommandsAuditService(DatabaseProvider databaseProvider)
        {
            this.databaseProvider = databaseProvider;
        }

        public async Task SaveExecutedCommandAuditRecord(string unitId, string userName, string command)
        {
            if (command.Length > Defaults.DefaultStringLength)
            {
                command = StringUtility.CutWithEnding(command, Defaults.DefaultStringLength);
            }

            ExecutedCommandAuditRecord executedCommandAuditRecord = new ExecutedCommandAuditRecord
            {
                Subject = userName,
                UnitId = unitId,
                Command = command
            };

            await databaseProvider.CreateAsync(executedCommandAuditRecord);
            await databaseProvider.CommitAsync();
        }
    }
}