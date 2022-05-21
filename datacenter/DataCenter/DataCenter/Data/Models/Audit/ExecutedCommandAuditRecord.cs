using System;
using System.ComponentModel.DataAnnotations;
using DataCenter.Common;
using DataCenter.Data.Attributes;
using DataCenter.Data.Base;

namespace DataCenter.Data.Models.Audit
{
    public class ExecutedCommandAuditRecord : BaseEntity, IUnited, ICreatedDate
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Subject { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UnitId { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Command { get; set; }

        public DateTime CreatedDate { get; set; }
    }
}