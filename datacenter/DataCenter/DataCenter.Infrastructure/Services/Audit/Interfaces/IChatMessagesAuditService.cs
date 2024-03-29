﻿using System.Threading.Tasks;

namespace DataCenter.Infrastructure.Services.Audit.Interfaces
{
    public interface IChatMessagesAuditService
    {
        Task SaveChatMessageAuditRecord(string unitId, string userName, string message);
    }
}
