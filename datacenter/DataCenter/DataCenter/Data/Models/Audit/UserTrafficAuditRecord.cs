using System;
using System.ComponentModel.DataAnnotations;
using DataCenter.Data.Base;
using DataCenter.Data.Enums;

namespace DataCenter.Data.Models.Audit
{
    public class UserTrafficAuditRecord : BaseEntity, IUnited, ICreatedDate
    {
        [Required]
        public string Subject { get; set; }

        [Required]
        public string UnitId { get; set; }

        [Required]
        public UserTrafficType UserTrafficType { get; set; }

        public DateTime CreatedDate { get; set; }
    }
}