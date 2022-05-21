using System;
using System.ComponentModel.DataAnnotations;
using DataCenter.Common;
using DataCenter.Data.Enums;
using DataCenter.Data.Attributes;
using DataCenter.Data.Base;

namespace DataCenter.Data.Models.Audit
{
    public class MoneyTransactionAuditRecord : BaseEntity, IUnited, ICreatedDate
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Subject { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UnitId { get; set; }

        [Required]
        public double Amount { get; set; }

        [Required]
        public TransactionType TransactionType { get; set; }

        [Required]
        public PaymentMethod TargetAccount { get; set; }

        public DateTime CreatedDate { get; set; }
    }
}