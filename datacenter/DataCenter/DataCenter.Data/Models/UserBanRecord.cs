using DataCenter.Data.Base;
using System;
using System.ComponentModel.DataAnnotations;

namespace DataCenter.Data.Models
{
    public class UserBanRecord : BaseEntity
    {
        [Required]
        public string UserName { get; set; }

        [Required]
        public string IssuerName { get; set; }

        [Required]
        public DateTime ReleaseDate { get; set; }

        [Required]
        public string Reason { get; set; }
    }
}
