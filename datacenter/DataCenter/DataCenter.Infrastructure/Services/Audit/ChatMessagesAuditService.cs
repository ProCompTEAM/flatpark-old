using DataCenter.Common;
using DataCenter.Data.Models.Audit;
using DataCenter.Infrastructure.Providers;
using DataCenter.Infrastructure.Providers.Interfaces;
using DataCenter.Infrastructure.Services.Audit.Interfaces;
using DataCenter.Infrastructure.Services.Interfaces;

using System.Threading.Tasks;

namespace DataCenter.Infrastructure.Services.Audit
{
    public class ChatMessagesAuditService : IChatMessagesAuditService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        public ChatMessagesAuditService(DatabaseProvider databaseProvider)
        {
            this.databaseProvider = databaseProvider;
        }

        public async Task SaveChatMessageAuditRecord(string unitId, string userName, string message)
        {
            if(message.Length > Defaults.DefaultStringLength)
            {
                message = StringUtility.CutWithEnding(message, Defaults.DefaultStringLength);
            }
            
            ChatMessageAuditRecord chatMessageAuditRecord = new ChatMessageAuditRecord
            {
                Subject = userName,
                UnitId = unitId,
                Message = message
            };

            await databaseProvider.CreateAsync(chatMessageAuditRecord);
            await databaseProvider.CommitAsync();
        }
    }
}