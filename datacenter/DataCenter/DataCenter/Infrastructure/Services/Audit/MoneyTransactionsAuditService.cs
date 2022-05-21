using DataCenter.Data.Enums;

using DataCenter.Infrastructure.Services.Audit.Interfaces;
using DataCenter.Infrastructure.Services.Interfaces;
using DataCenter.Infrastructure.Providers;
using DataCenter.Infrastructure.Providers.Interfaces;

using DataCenter.Data.Models.Audit;

using System.Threading.Tasks;

namespace DataCenter.Infrastructure.Services.Audit
{
    public class MoneyTransactionsAuditService : IMoneyTransactionsAuditService, IService
    {
        private readonly IDatabaseProvider databaseProvider;

        public MoneyTransactionsAuditService(DatabaseProvider databaseProvider)
        {
            this.databaseProvider = databaseProvider;
        }

        public async Task ProcessGiveOperation(string unitId, string userName, double amount, PaymentMethod paymentMethod)
        {
            await CreateTransaction(userName, unitId, amount, paymentMethod, TransactionType.Give);
        }

        public async Task ProcessReduceOperation(string unitId, string userName, double amount, PaymentMethod paymentMethod)
        {
            await CreateTransaction(unitId, userName, amount, paymentMethod, TransactionType.Reduce);
        }

        private async Task CreateTransaction(string unitId, string userName, double amount, PaymentMethod targetAccount, TransactionType transactionType)
        {
            MoneyTransactionAuditRecord moneyTransactionAuditRecord = new MoneyTransactionAuditRecord
            {
                Subject = userName,
                UnitId = unitId,
                Amount = amount,
                TransactionType = transactionType,
                TargetAccount = targetAccount
            };

            await databaseProvider.CreateAsync(moneyTransactionAuditRecord);
        }
    }
}