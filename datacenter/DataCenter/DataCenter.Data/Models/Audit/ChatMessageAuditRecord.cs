using System;
using System.ComponentModel.DataAnnotations;
using DataCenter.Data.Base;

namespace DataCenter.Data.Models.Audit
{
    public class ChatMessageAuditRecord : BaseEntity, IUnited, ICreatedDate
    {
        [Required]
        public string Subject { get; set; }

        [Required]
        public string UnitId { get; set; }

        [Required]
        public string Message { get; set; }

        public DateTime CreatedDate { get; set; }
    }
}