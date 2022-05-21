using DataCenter.Common;
using DataCenter.Data.Attributes;
using DataCenter.Data.Base;
using System;
using System.ComponentModel.DataAnnotations;

namespace DataCenter.Data.Models
{
    public class UserBanRecord : BaseEntity
    {
        [Required, Unicode(Defaults.DefaultStringLength)]
        public string UserName { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string IssuerName { get; set; }

        [Required]
        public DateTime ReleaseDate { get; set; }

        [Required, Unicode(Defaults.DefaultStringLength)]
        public string Reason { get; set; }
    }
}
